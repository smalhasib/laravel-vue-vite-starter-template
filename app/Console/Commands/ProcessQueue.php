<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ProcessQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process {--times=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queued jobs manually';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $times = (int) $this->option('times');

        $this->info("Processing queue {$times} time(s)...");

        for ($i = 0; $i < $times; $i++) {
            $this->info("Run #" . ($i + 1));
            Artisan::call('queue:work', [
                '--once' => true,
                '--queue' => 'default',
            ]);

            $output = Artisan::output();
            $this->info($output);

            // If we're processing multiple times, wait a bit between runs
            if ($i < $times - 1) {
                sleep(1);
            }
        }

        $this->info('Done processing queue.');
    }
}
