<?php

namespace App\Listeners;

use App\Events\TranslationUpdated;
use App\Services\TranslationCacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ClearTranslationCache implements ShouldQueue
{
    use InteractsWithQueue;
    
    protected $translationCacheService;

    public function __construct(TranslationCacheService $translationCacheService)
    {
        $this->translationCacheService = $translationCacheService;
    }

    public function handle(TranslationUpdated $event)
    {
        try {
            Log::info('Clearing translation cache', [
                'translation_id' => $event->translation->id,
                'locale' => $event->translation->locale,
                'tag' => $event->translation->tag,
                'old_locale' => $event->oldLocale,
                'old_tag' => $event->oldTag
            ]);
            
            // Clear the cache for the old locale and tag (if they changed)
            if ($event->oldLocale && $event->oldTag) {
                $this->translationCacheService->clearCache($event->oldLocale, $event->oldTag);
            }
            
            // Clear the cache for the new locale and tag
            $this->translationCacheService->clearCache($event->translation->locale, $event->translation->tag);
            
            Log::info('Translation cache cleared successfully');
        } catch (\Exception $e) {
            Log::error('Failed to clear translation cache: ' . $e->getMessage(), [
                'translation_id' => $event->translation->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}