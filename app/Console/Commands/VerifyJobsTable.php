<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyJobsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify that the jobs table exists and has the correct structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Verifying jobs table...");

        if (!Schema::hasTable('jobs')) {
            $this->error("Jobs table does not exist!");
            $this->info("Run migrations to create it: php artisan migrate");
            return 1;
        }

        $requiredColumns = [
            'id' => 'bigint',
            'queue' => 'varchar',
            'payload' => 'longtext',
            'attempts' => 'tinyint',
            'reserved_at' => 'int',
            'available_at' => 'int',
            'created_at' => 'int'
        ];

        $columns = DB::select("DESCRIBE jobs");
        $columnMap = [];

        foreach ($columns as $column) {
            $columnMap[$column->Field] = $column->Type;
        }

        $this->info("Jobs table exists with columns:");
        foreach ($columnMap as $column => $type) {
            $this->line(" - {$column} ({$type})");
        }

        $missingColumns = array_diff_key($requiredColumns, $columnMap);

        if (!empty($missingColumns)) {
            $this->error("Missing columns: " . implode(', ', array_keys($missingColumns)));
            $this->info("Run migrations to add missing columns: php artisan migrate");
            return 1;
        }

        $this->info("Database queue ready!");

        // Test job insertion
        $this->info("\nTesting job insertion...");

        try {
            $payload = json_encode([
                'displayName' => 'TestJob',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'maxTries' => 1,
                'timeout' => 60,
                'data' => [
                    'commandName' => 'App\\Jobs\\TestJob',
                    'command' => serialize((object)['test' => true])
                ]
            ]);

            $id = DB::table('jobs')->insertGetId([
                'queue' => 'default',
                'payload' => $payload,
                'attempts' => 0,
                'reserved_at' => null,
                'available_at' => time(),
                'created_at' => time(),
            ]);

            $this->info("Test job inserted with ID: {$id}");

            // Clean up
            DB::table('jobs')->where('id', $id)->delete();
            $this->info("Test job deleted");

            $this->info("\nQueue system is working correctly!");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to insert test job: {$e->getMessage()}");
            return 1;
        }
    }
}
