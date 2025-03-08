<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class QueueStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:status {queue=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display the status of a queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queue = $this->argument('queue');
        $queueKey = 'queues:' . $queue;

        $this->info("Checking queue: {$queue}");

        try {
            $redis = Redis::connection();
            $length = $redis->llen($queueKey);

            if ($length > 0) {
                $this->info("There are {$length} jobs in the queue");

                // Show some sample jobs
                $maxSamples = min($length, 3);
                $this->info("Showing first {$maxSamples} jobs:");

                for ($i = 0; $i < $maxSamples; $i++) {
                    $job = $redis->lindex($queueKey, $i);
                    if ($job) {
                        $this->line("Job #" . ($i + 1) . ": " . substr($job, 0, 100) . '...');
                    }
                }
            } else {
                $this->info("The queue is empty");
            }
        } catch (\Exception $e) {
            $this->error("Error connecting to Redis: " . $e->getMessage());
        }
    }
}
