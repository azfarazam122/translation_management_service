<?php

namespace App\Models;

use App\Events\TranslationUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'key',
        'locale',
        'tag',
        'value',
    ];
    
    protected $dispatchesEvents = [
        'created' => TranslationUpdated::class,
        'deleted' => TranslationUpdated::class,
    ];
    
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::updated(function ($translation) {
            // Fire event with old values for cache updating
            TranslationUpdated::dispatch(
                $translation, 
                $translation->getOriginal('locale'), 
                $translation->getOriginal('tag')
            );
        });
        
        static::created(function ($translation) {
            // Fire event for created translations
            TranslationUpdated::dispatch($translation);
        });
        
        static::deleted(function ($translation) {
            // Fire event for deleted translations
            TranslationUpdated::dispatch($translation);
        });
    }
}