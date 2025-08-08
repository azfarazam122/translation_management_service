<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationCacheService
{
    /**
     * Cache key prefix for translations
     */
    protected const CACHE_PREFIX = 'translations:';
    
    /**
     * Cache TTL in seconds (5 minutes)
     */
    protected const CACHE_TTL = 300;
    
    /**
     * Get all translations grouped by locale and tag
     * 
     * @param string|null $locale
     * @param string|null $tag
     * @return array
     */
    public function getTranslations($locale = null, $tag = null)
    {
        $cacheKey = $this->getCacheKey($locale, $tag);
        
        Log::info('Getting translations', [
            'cache_key' => $cacheKey,
            'locale' => $locale,
            'tag' => $tag
        ]);
        
        // Use Laravel's Cache facade which supports multiple drivers
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($locale, $tag) {
            Log::info('Cache miss, getting from database', [
                'locale' => $locale,
                'tag' => $tag
            ]);
            
            return $this->getTranslationsFromDatabase($locale, $tag);
        });
    }
    
    /**
     * Clear the translations cache and optionally reload it
     * 
     * @param string|null $locale
     * @param string|null $tag
     * @param bool $reload Whether to reload the cache immediately
     * @return void
     */
    public function clearCache($locale = null, $tag = null, $reload = true)
    {
        if ($locale && $tag) {
            // Clear specific locale and tag cache
            Cache::forget($this->getCacheKey($locale, $tag));
            
            if ($reload) {
                // Reload the cache by calling getTranslations which will rebuild it
                $this->getTranslations($locale, $tag);
            }
        } elseif ($locale) {
            // Clear all caches for this locale
            $this->clearLocaleCache($locale, $reload);
        } else {
            // Clear all translation caches
            $this->clearAllCache($reload);
        }
    }
    
    /**
     * Get translations from database efficiently
     * 
     * @param string|null $locale
     * @param string|null $tag
     * @return array
     */
    protected function getTranslationsFromDatabase($locale = null, $tag = null)
    {
        $query = Translation::query();
        
        if ($locale) {
            $query->where('locale', $locale);
        }
        
        if ($tag) {
            $query->where('tag', $tag);
        }
        
        // Use select with specific columns for better performance
        $query->select(['key', 'locale', 'tag', 'value']);
        
        // Log the query for debugging
        Log::info('Translation query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'locale' => $locale,
            'tag' => $tag
        ]);
        
        // Use cursor for memory efficiency with large datasets
        $groupedTranslations = [];
        $count = 0;
        
        foreach ($query->cursor() as $translation) {
            if (!isset($groupedTranslations[$translation->locale])) {
                $groupedTranslations[$translation->locale] = [];
            }
            
            if (!isset($groupedTranslations[$translation->locale][$translation->tag])) {
                $groupedTranslations[$translation->locale][$translation->tag] = [];
            }
            
            $groupedTranslations[$translation->locale][$translation->tag][$translation->key] = $translation->value;
            $count++;
        }
        
        Log::info('Translation query result', [
            'count' => $count,
            'locales' => array_keys($groupedTranslations),
            'locale' => $locale,
            'tag' => $tag
        ]);
        
        return $groupedTranslations;
    }
    
    /**
     * Clear all caches for a specific locale
     * 
     * @param string $locale
     * @param bool $reload
     * @return void
     */
    protected function clearLocaleCache($locale, $reload = true)
    {
        // Get all unique tags for this locale
        $tags = Translation::where('locale', $locale)->distinct()->pluck('tag');
        
        foreach ($tags as $tag) {
            Cache::forget($this->getCacheKey($locale, $tag));
            
            if ($reload) {
                $this->getTranslations($locale, $tag);
            }
        }
        
        // Also clear the general locale cache
        Cache::forget($this->getCacheKey($locale));
        
        if ($reload) {
            $this->getTranslations($locale);
        }
    }
    
    /**
     * Clear all translation caches
     * 
     * @param bool $reload
     * @return void
     */
    protected function clearAllCache($reload = true)
    {
        // Get all unique locales
        $locales = Translation::distinct()->pluck('locale');
        
        foreach ($locales as $locale) {
            $this->clearLocaleCache($locale, false); // Don't reload yet to avoid too many queries
        }
        
        // Clear the general cache
        Cache::forget($this->getCacheKey());
        
        if ($reload) {
            $this->getTranslations(); // Reload the general cache
        }
    }
    
    /**
     * Generate cache key
     * 
     * @param string|null $locale
     * @param string|null $tag
     * @return string
     */
    protected function getCacheKey($locale = null, $tag = null)
    {
        $key = self::CACHE_PREFIX;
        
        if ($locale) {
            $key .= $locale;
            
            if ($tag) {
                $key .= ':' . $tag;
            }
        }
        
        return $key;
    }
    
    /**
     * Warm up all translation caches
     * This is called when the queue worker starts
     * 
     * @return void
     */
    public function warmUpAllCaches()
    {
        Log::info('Starting to warm up all translation caches');
        
        // Get all unique locales
        $locales = Translation::distinct()->pluck('locale');
        
        Log::info('Found ' . $locales->count() . ' locales');
        
        $totalLocales = $locales->count();
        $currentLocale = 0;
        
        foreach ($locales as $locale) {
            $currentLocale++;
            $localeStartTime = microtime(true);
            
            Log::info("Processing locale {$currentLocale}/{$totalLocales}: {$locale}");
            
            // Warm up the locale cache
            $this->getTranslations($locale);
            
            // Get all unique tags for this locale
            $tags = Translation::where('locale', $locale)->distinct()->pluck('tag');
            
            Log::info('Found ' . $tags->count() . ' tags for locale: ' . $locale);
            
            $totalTags = $tags->count();
            $currentTag = 0;
            
            foreach ($tags as $tag) {
                $currentTag++;
                
                Log::info("Processing tag {$currentTag}/{$totalTags} for locale {$locale}: {$tag}");
                
                // Warm up the locale+tag cache
                $this->getTranslations($locale, $tag);
            }
            
            $localeEndTime = microtime(true);
            $localeExecutionTime = round($localeEndTime - $localeStartTime, 2);
            
            Log::info("Completed locale {$locale} in {$localeExecutionTime} seconds");
        }
        
        // Warm up the general cache
        Log::info('Warming up general cache');
        $generalStartTime = microtime(true);
        $this->getTranslations();
        $generalEndTime = microtime(true);
        $generalExecutionTime = round($generalEndTime - $generalStartTime, 2);
        Log::info("General cache warmed up in {$generalExecutionTime} seconds");
        
        Log::info('All translation caches warmed up successfully');
        
        return true;
    }
}