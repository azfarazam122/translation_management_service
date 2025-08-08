<?php

namespace App\Console\Commands;

use App\Services\TranslationCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StartQueueWithCache extends Command
{
    protected $signature = 'queue:start-with-cache {--queue=default : The queue to work}';
    protected $description = 'Initialize cache and start the queue worker';

    public function handle()
    {
        $queue = $this->option('queue');
        
        $this->info('Initializing translation cache...');
        Log::info('Initializing translation cache from StartQueueWithCache command');
        
        $startTime = microtime(true);
        
        try {
            // Initialize cache directly
            $cacheService = app(TranslationCacheService::class);
            $result = $cacheService->warmUpAllCaches();
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            $this->info('Cache initialized successfully in ' . $executionTime . ' seconds.');
            Log::info('Cache initialized successfully', ['result' => $result, 'execution_time' => $executionTime]);
        } catch (\Exception $e) {
            $this->error('Failed to initialize cache: ' . $e->getMessage());
            Log::error('Failed to initialize cache: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Continue to start the queue worker even if cache initialization fails
        }
        
        $this->info('Starting queue worker...');
        Log::info('Starting queue worker', ['queue' => $queue]);
        
        // Start the queue worker
        $this->call('queue:work', [
            '--queue' => $queue,
            '--verbose' => true,
        ]);
    }
}