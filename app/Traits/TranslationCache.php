<?php

namespace App\Traits;

trait TranslationCache
{
    public static function bootTranslationCache()
    {
        static::saved(function () {
            cache()->forget('translations_export');
        });

        static::deleted(function () {
            cache()->forget('translations_export');
        });
    }
}