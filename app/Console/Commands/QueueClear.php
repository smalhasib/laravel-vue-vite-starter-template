<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class QueueClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:clear {queue=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all jobs from a queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queue = $this->argument('queue');
        $queueKey = 'queues:' . $queue;

        $this->info("Clearing queue: {$queue}");

        try {
            $redis = Redis::connection();
            $count = $redis->llen($queueKey);

            if ($count > 0) {
                $redis->del($queueKey);
                $this->info("Successfully cleared {$count} jobs from the queue.");
            } else {
                $this->info("Queue is already empty.");
            }
        } catch (\Exception $e) {
            $this->error("Error clearing queue: " . $e->getMessage());
        }
    }
}
