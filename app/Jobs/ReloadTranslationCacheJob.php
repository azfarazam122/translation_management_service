<?php

namespace App\Jobs;

use App\Services\TranslationCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReloadTranslationCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $locale;
    protected $tag;
    public $tries = 3;
    public $backoff = [5, 10, 20];

    public function __construct($locale = null, $tag = null)
    {
        $this->locale = $locale;
        $this->tag = $tag;
        Log::info('ReloadTranslationCacheJob created', ['locale' => $locale, 'tag' => $tag]);
    }

    public function handle()
    {
        Log::info('ReloadTranslationCacheJob started', ['locale' => $this->locale, 'tag' => $this->tag]);
        
        try {
            $cacheService = app(TranslationCacheService::class);
            
            // Clear and reload the cache
            $cacheService->clearCache($this->locale, $this->tag, true);
            
            Log::info('ReloadTranslationCacheJob completed', ['locale' => $this->locale, 'tag' => $this->tag]);
        } catch (\Exception $e) {
            Log::error('Failed to reload translation cache: ' . $e->getMessage(), [
                'locale' => $this->locale,
                'tag' => $this->tag,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->fail($e);
        }
    }
}