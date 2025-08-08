<?php

namespace App\Jobs;

use App\Services\TranslationCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WarmTranslationCacheJob implements ShouldQueue
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
        Log::info('WarmTranslationCacheJob created', ['locale' => $locale, 'tag' => $tag]);
    }

    public function handle()
    {
        Log::info('WarmTranslationCacheJob started', ['locale' => $this->locale, 'tag' => $this->tag]);
        
        try {
            $cacheService = app(TranslationCacheService::class);
            
            // Get translations to warm up the cache
            $result = $cacheService->getTranslations($this->locale, $this->tag);
            
            Log::info('WarmTranslationCacheJob completed', [
                'locale' => $this->locale, 
                'tag' => $this->tag,
                'result_count' => is_array($result) ? count($result) : 0
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to warm translation cache: ' . $e->getMessage(), [
                'locale' => $this->locale,
                'tag' => $this->tag,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->fail($e);
        }
    }
}