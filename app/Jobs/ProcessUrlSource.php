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
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Source $source,
        public ?string $url = null
    ) {
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
        $url = $this->url ?? $this->source->title; // Use provided URL or source title

        if (!$url) {
            throw new \Exception('URL not found');
        }

        // Scrape the URL
        Log::info("Scraping URL: {$url}", ['source_id' => $this->source->id]);
        $scrapedData = $scrapingService->scrapeUrl($url);

        // Create document
        $document = $this->source->documents()->create([
            'title' => $scrapedData['title'] ?? '',
            'content' => $scrapedData['content'],
            'source' => $url
        ]);

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
        $urls = explode("\n", $this->source->title); // URLs are now stored in title, one per line

        if (empty($urls)) {
            throw new \Exception('No URLs found in source');
        }

        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url))
                continue;

            try {
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
                }

                Log::info("URL from list processed successfully", [
                    'source_id' => $this->source->id,
                    'document_id' => $document->id,
                    'url' => $url,
                    'chunks' => $indexingResult['chunks']
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to process URL from list", [
                    'source_id' => $this->source->id,
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
                // Continue with next URL even if one fails
            }
        }
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
