<?php

namespace App\Jobs;

use App\Services\IndexingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteRemoteSource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected int $botId;
    protected int $sourceId;
    protected array $documents;

    public function __construct(int $userId, int $botId, int $sourceId, array $documents)
    {
        $this->userId = $userId;
        $this->botId = $botId;
        $this->sourceId = $sourceId;
        $this->documents = $documents;
    }

    public function handle(IndexingService $indexingService)
    {
        try {
            $response = $indexingService->deleteSource(
                $this->userId,
                $this->botId,
                $this->sourceId,
                $this->documents
            );

            Log::info('Remote source deleted successfully', [
                'source_id' => $this->sourceId,
                'bot_id' => $this->botId,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete remote source', [
                'source_id' => $this->sourceId,
                'bot_id' => $this->botId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
