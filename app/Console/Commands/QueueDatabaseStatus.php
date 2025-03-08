<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueueDatabaseStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:dbstatus {queue=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display the status of database queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queue = $this->argument('queue');

        $this->info("Checking database queue: {$queue}");

        try {
            // Check if jobs table exists
            if (!Schema::hasTable('jobs')) {
                $this->error("Jobs table doesn't exist. Run migrations first.");
                return 1;
            }

            // Get all jobs in the queue
            $totalJobs = DB::table('jobs')
                ->where('queue', $queue)
                ->count();

            $availableJobs = DB::table('jobs')
                ->where('queue', $queue)
                ->whereNull('reserved_at')
                ->count();

            $reservedJobs = DB::table('jobs')
                ->where('queue', $queue)
                ->whereNotNull('reserved_at')
                ->count();

            $this->info("Total jobs: $totalJobs");
            $this->info("Available jobs: $availableJobs");
            $this->info("Reserved jobs: $reservedJobs");

            // Get pending jobs details
            if ($availableJobs > 0) {
                $this->info("\nFirst few pending jobs:");

                $jobs = DB::table('jobs')
                    ->where('queue', $queue)
                    ->whereNull('reserved_at')
                    ->orderBy('id')
                    ->limit(3)
                    ->get();

                foreach ($jobs as $index => $job) {
                    $payload = json_decode($job->payload, true);
                    $jobName = $payload['displayName'] ?? 'Unknown';
                    $this->info("#{$index} - {$jobName} (ID: {$job->id}, Attempts: {$job->attempts})");
                }
            }

            // Check failed jobs
            $failedCount = DB::table('failed_jobs')->count();
            $this->info("\nFailed jobs: $failedCount");

            if ($failedCount > 0) {
                $this->info("\nMost recent failed jobs:");
                $failedJobs = DB::table('failed_jobs')
                    ->orderBy('id', 'desc')
                    ->limit(3)
                    ->get();

                foreach ($failedJobs as $index => $job) {
                    $payload = json_decode($job->payload, true);
                    $jobName = $payload['displayName'] ?? 'Unknown';
                    $this->info("#{$index} - {$jobName} (ID: {$job->id}, Failed at: {$job->failed_at})");
                    $this->line("Reason: " . substr($job->exception, 0, 100) . '...');
                }

                $this->line("\nTo retry failed jobs, run: php artisan queue:retry all");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error checking database queue: " . $e->getMessage());
            return 1;
        }
    }
}
