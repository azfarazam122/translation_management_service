<?php

namespace App\Console\Commands;

use App\Jobs\InitializeCacheOnQueueStart;
use Illuminate\Console\Command;

class StartQueueWorkerWithCache extends Command
{
    protected $signature = 'queue:work-with-cache {--queue=default : The queue to work}';
    protected $description = 'Start the queue worker and initialize the cache';

    public function handle()
    {
        $queue = $this->option('queue');
        
        $this->info('Initializing translation cache...');
        
        // Dispatch the cache initialization job
        InitializeCacheOnQueueStart::dispatch()
            ->onQueue($queue);
        
        $this->info('Cache initialization job dispatched. Starting queue worker...');
        
        // Start the queue worker
        $this->call('queue:work', [
            '--queue' => $queue,
            '--verbose' => true,
        ]);
    }
}