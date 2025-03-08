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

class ProcessDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        protected Source $source,
        protected string $url
    ) {
    }

    public function handle(ScrapingService $scrapingService, IndexingService $indexingService): void
    {
        try {
            // Update source status to indexing if not already
            if ($this->source->status !== Source::STATUS_INDEXING) {
                $this->source->setIndexing();
            }

            // Scrape the URL
            Log::info("Scraping URL for document: {$this->url}", ['source_id' => $this->source->id]);
            $scrapedData = $scrapingService->scrapeUrl($this->url);

            // Create document with scraped data
            $document = $this->source->documents()->create([
                'title' => $scrapedData['title'] ?? 'Untitled',
                'content' => $scrapedData['content'],
                'source' => $this->url,
                'indexed_chunks_count' => 0
            ]);

            // Index the document
            Log::info("Indexing document: {$document->id}");
            $indexingResult = $indexingService->indexData($this->source->bot_id, [
                'user_id' => $this->source->user_id,
                'source_id' => $this->source->id,
                'document_id' => $document->id,
                'title' => $document->title,
                'url' => $document->source,
                'content' => $document->content
            ]);

            if ($indexingResult['status'] !== 'success') {
                throw new \Exception('Failed to index document: ' . ($indexingResult['error'] ?? 'Unknown error'));
            }

            // Update document chunks count
            $document->update([
                'indexed_chunks_count' => $indexingResult['chunks']
            ]);

            // Check if all documents are processed and update source status
            $pendingDocuments = $this->source->documents()
                ->where('indexed_chunks_count', 0)
                ->count();

            if ($pendingDocuments === 0) {
                $this->source->setIndexed();
            }

            Log::info("Document processed successfully", [
                'source_id' => $this->source->id,
                'document_id' => $document->id,
                'chunks' => $indexingResult['chunks']
            ]);

        } catch (\Exception $e) {
            Log::error("Error processing document for source ID {$this->source->id}: " . $e->getMessage(), [
                'url' => $this->url,
                'trace' => $e->getTraceAsString()
            ]);

            // Only set source as failed if this is the only document or all other documents failed
            $successfulDocuments = $this->source->documents()
                ->where('indexed_chunks_count', '>', 0)
                ->count();

            if ($successfulDocuments === 0) {
                $this->source->setFailed();
            }

            throw $e;
        }
    }
}
