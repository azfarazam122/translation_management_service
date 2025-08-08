<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    /** @use HasFactory<\Database\Factories\TranslationFactory> */
    use HasFactory;
    use \App\Traits\TranslationCache;
    
    protected $fillable = [
        'key',
        'locale',
        'tag',
        'value',
    ];
}
