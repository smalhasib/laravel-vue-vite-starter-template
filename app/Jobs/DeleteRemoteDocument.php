<?php

namespace App\Jobs;

use App\Services\IndexingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteRemoteDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $userId,
        protected int $botId,
        protected int $sourceId,
        protected int $documentId,
        protected int $chunksCount
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(IndexingService $indexingService): void
    {
        try {
            $response = $indexingService->deleteDocument(
                $this->userId,
                $this->botId,
                $this->sourceId,
                $this->documentId,
                $this->chunksCount
            );

            Log::info('Remote document deleted successfully', [
                'document_id' => $this->documentId,
                'source_id' => $this->sourceId,
                'bot_id' => $this->botId,
                'chunks_removed' => $response['deletedChunks'] ?? $this->chunksCount,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete remote document', [
                'document_id' => $this->documentId,
                'source_id' => $this->sourceId,
                'bot_id' => $this->botId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
