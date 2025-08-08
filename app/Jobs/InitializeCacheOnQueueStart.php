<?php

namespace App\Jobs;

use App\Services\TranslationCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InitializeCacheOnQueueStart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = [5, 10, 20];

    public function __construct()
    {
        Log::info('InitializeCacheOnQueueStart created');
    }

    public function handle()
    {
        Log::info('InitializeCacheOnQueueStart started');
        
        try {
            $cacheService = app(TranslationCacheService::class);
            
            // Warm up all caches
            $result = $cacheService->warmUpAllCaches();
            
            Log::info('InitializeCacheOnQueueStart completed', ['result' => $result]);
        } catch (\Exception $e) {
            Log::error('Failed to initialize translation cache: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->fail($e);
        }
    }
}