<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSource;
use Illuminate\Console\Command;
use App\Models\Source;
use Illuminate\Support\Facades\DB;

class TestQueueDispatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:queue {sourceId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test queue job dispatch with a source';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sourceId = $this->argument('sourceId');

        if (!$sourceId) {
            // Find a source to test with
            $source = Source::first();
            if (!$source) {
                $this->error('No sources found in the database.');
                return 1;
            }
            $sourceId = $source->id;
        } else {
            $source = Source::find($sourceId);
            if (!$source) {
                $this->error("Source with ID {$sourceId} not found.");
                return 1;
            }
        }

        $this->info("Testing queue dispatch with source ID: {$sourceId}");

        try {
            // Test dispatching a job
            ProcessSource::dispatch($sourceId)
                ->onQueue('default');

            $this->info("Job dispatched successfully to 'default' queue.");

            // Check if the job was added to the database
            $jobCount = DB::table('jobs')->count();
            $this->info("Current jobs in database: {$jobCount}");

            $this->info("\nTo process this job, run: php artisan queue:work --queue=default");

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to dispatch job: " . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
