<?php

namespace App\Jobs;

use App\Models\Source;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteRemoteBot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected int $botId;
    protected array $sources;

    public function __construct(int $userId, int $botId, array $sources)
    {
        $this->userId = $userId;
        $this->botId = $botId;
        $this->sources = $sources;
    }

    public function handle()
    {
        try {
            foreach ($this->sources as $source) {
                DeleteRemoteSource::dispatch(
                    $this->userId,
                    $this->botId,
                    $source['sourceId'],
                    $source['documents']
                );
            }

            Log::info('Remote bot deletion queued', [
                'bot_id' => $this->botId,
                'sources_count' => count($this->sources)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue remote bot deletion', [
                'bot_id' => $this->botId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
