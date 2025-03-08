<?php

namespace App\Jobs;

use App\Models\Source;
use App\Services\ScrapingService;
use App\Services\IndexingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ProcessUrlSource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     * Increased to accommodate delays between URL processing
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Source $source,
        public ?string $url = null
    ) {
    }

    /**
     * Validate if a URL is accessible and returns valid content
     */
    private function isValidUrl(string $url): bool
    {
        try {
            // Basic URL format validation
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                Log::warning("Invalid URL format", ['url' => $url]);
                return false;
            }

            // Parse URL to check scheme and host
            $parsedUrl = parse_url($url);
            if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
                Log::warning("URL missing scheme or host", ['url' => $url]);
                return false;
            }

            // Check if scheme is http or https
            if (!in_array($parsedUrl['scheme'], ['http', 'https'])) {
                Log::warning("Invalid URL scheme", ['url' => $url, 'scheme' => $parsedUrl['scheme']]);
                return false;
            }

            // Try to make a HEAD request to check if URL is accessible
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; FluentBot/1.0; +http://example.com)'
            ])->timeout(10)->head($url);

            // Check if response is successful and content type is HTML
            if (!$response->successful()) {
                Log::warning("URL not accessible", ['url' => $url, 'status' => $response->status()]);
                return false;
            }

            $contentType = $response->header('Content-Type');
            if (!str_contains(strtolower($contentType), 'text/html')) {
                Log::warning("URL does not return HTML content", ['url' => $url, 'content_type' => $contentType]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::warning("Error validating URL", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Execute the job.
     */
    public function handle(ScrapingService $scrapingService, IndexingService $indexingService): void
    {
        try {
            // Update status to indexing
            $this->source->setIndexing();

            // Process based on source type
            switch ($this->source->type) {
                case 'URL':
                    $this->processUrl($scrapingService, $indexingService);
                    break;
                case 'URL List':
                    $this->processUrlList($scrapingService, $indexingService);
                    break;
                case 'WordPress':
                    $this->processWordPress($scrapingService, $indexingService);
                    break;
                default:
                    throw new \Exception("Unsupported source type: {$this->source->type}");
            }

            // Update source with success status
            $this->source->update([
                'status' => Source::STATUS_INDEXED,
                'last_refresh_at' => now(),
                'next_refresh_at' => $this->calculateNextRefresh()
            ]);

            Log::info("Source processed successfully", ['source_id' => $this->source->id]);

        } catch (\Exception $e) {
            Log::error("Error processing source ID {$this->source->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Update status to failed
            $this->source->setFailed();

            throw $e;
        }
    }

    /**
     * Process URL type source
     */
    private function processUrl(ScrapingService $scrapingService, IndexingService $indexingService): void
    {
        $url = $this->url ?? $this->source->title;

        if (!$url) {
            throw new \Exception('URL not found');
        }

        // Validate URL before processing
        if (!$this->isValidUrl($url)) {
            throw new \Exception("Invalid or inaccessible URL: {$url}");
        }

        // Scrape the URL
        Log::info("Scraping URL: {$url}", ['source_id' => $this->source->id]);
        $scrapedData = $scrapingService->scrapeUrl($url);

        // Ensure we have a valid title from either user input or scraped data
        $documentTitle = $scrapedData['title'] ?? 'Untitled';

        // Create document
        $document = $this->source->documents()->create([
            'title' => $documentTitle,
            'content' => $scrapedData['content'],
            'source' => $url
        ]);

        // Update source title if empty
        if (empty($this->source->title)) {
            $this->source->update([
                'title' => $documentTitle
            ]);
        }

        // Index the data
        Log::info("Indexing data for document ID: {$document->id}");
        $indexingResult = $indexingService->indexData($this->source->bot_id, [
            'user_id' => $this->source->user_id,
            'source_id' => $this->source->id,
            'document_id' => $document->id,
            'title' => $document->title,
            'url' => $document->source,
            'content' => $document->content
        ]);

        if ($indexingResult['status'] !== 'success') {
            throw new \Exception('Failed to index data: ' . ($indexingResult['error'] ?? 'Unknown error'));
        }

        // Update the document's indexed chunks count
        $document->update([
            'indexed_chunks_count' => $indexingResult['chunks']
        ]);

        Log::info("URL content imported successfully", [
            'source_id' => $this->source->id,
            'document_id' => $document->id,
            'chunks' => $indexingResult['chunks']
        ]);
    }

    /**
     * Process URL List type source
     */
    private function processUrlList(ScrapingService $scrapingService, IndexingService $indexingService): void
    {
        // Get file path from source data
        $filePath = $this->source->data['file_path'] ?? null;
        if (!$filePath || !Storage::disk('local')->exists($filePath)) {
            throw new \Exception('Source file not found');
        }

        // Read and parse file contents
        $contents = Storage::disk('local')->get($filePath);
        $allUrls = array_filter(explode("\n", $contents), 'trim');

        // Filter and validate URLs
        $validUrls = [];
        $invalidUrls = [];

        foreach ($allUrls as $url) {
            $url = trim($url);
            if (empty($url))
                continue;

            if ($this->isValidUrl($url)) {
                $validUrls[] = $url;
            } else {
                $invalidUrls[] = $url;
            }
        }

        if (empty($validUrls)) {
            throw new \Exception('No valid URLs found in source file');
        }

        Log::info("URL validation completed", [
            'source_id' => $this->source->id,
            'total_urls' => count($allUrls),
            'valid_urls' => count($validUrls),
            'invalid_urls' => count($invalidUrls)
        ]);

        $processedCount = 0;
        $failedUrls = [];

        foreach ($validUrls as $url) {
            try {
                // Add a delay between requests (except for the first one)
                if ($processedCount > 0) {
                    Log::info("Waiting 5 seconds before processing next URL", [
                        'source_id' => $this->source->id,
                        'next_url' => $url
                    ]);
                    sleep(5);
                }

                // Scrape the URL
                Log::info("Scraping URL from list: {$url}", ['source_id' => $this->source->id]);
                $scrapedData = $scrapingService->scrapeUrl($url);

                // Create document
                $document = $this->source->documents()->create([
                    'title' => $scrapedData['title'] ?? $url,
                    'content' => $scrapedData['content'],
                    'source' => $url
                ]);

                // Index the data
                $indexingResult = $indexingService->indexData($this->source->bot_id, [
                    'user_id' => $this->source->user_id,
                    'source_id' => $this->source->id,
                    'document_id' => $document->id,
                    'title' => $document->title,
                    'url' => $document->source,
                    'content' => $document->content
                ]);

                if ($indexingResult['status'] === 'success') {
                    $document->update([
                        'indexed_chunks_count' => $indexingResult['chunks']
                    ]);
                    $processedCount++;
                }

                Log::info("URL from list processed successfully", [
                    'source_id' => $this->source->id,
                    'document_id' => $document->id,
                    'url' => $url,
                    'chunks' => $indexingResult['chunks'],
                    'processed_count' => $processedCount,
                    'remaining_urls' => count($validUrls) - $processedCount
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to process URL from list", [
                    'source_id' => $this->source->id,
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
                $failedUrls[] = $url;
            }
        }

        // Update source data with processing results
        $this->source->update([
            'data' => array_merge($this->source->data, [
                'total_urls' => count($allUrls),
                'valid_urls' => count($validUrls),
                'invalid_urls' => count($invalidUrls),
                'invalid_urls_list' => $invalidUrls,
                'processed_urls' => $processedCount,
                'failed_urls' => count($failedUrls),
                'failed_urls_list' => $failedUrls
            ])
        ]);

        // If no URLs were processed successfully, mark the source as failed
        if ($processedCount === 0) {
            throw new \Exception('Failed to process any URLs from the list');
        }

        // Log summary
        Log::info("URL List processing completed", [
            'source_id' => $this->source->id,
            'total_urls' => count($allUrls),
            'valid_urls' => count($validUrls),
            'invalid_urls' => count($invalidUrls),
            'processed' => $processedCount,
            'failed' => count($failedUrls)
        ]);
    }

    /**
     * Process WordPress type source
     */
    private function processWordPress(ScrapingService $scrapingService, IndexingService $indexingService): void
    {
        $xmlFilePath = $this->source->data['xml_file_path'] ?? null;

        if (!$xmlFilePath) {
            throw new \Exception('XML file path not found in source data');
        }

        // TODO: Implement WordPress XML processing
        throw new \Exception('WordPress source processing not implemented yet');
    }

    /**
     * Calculate the next refresh timestamp based on the schedule.
     */
    private function calculateNextRefresh()
    {
        return match ($this->source->refresh_schedule) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            default => null,
        };
    }
}

