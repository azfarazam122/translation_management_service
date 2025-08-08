<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Translation;

class TranslationUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $translation;
    public $oldLocale;
    public $oldTag;

    public function __construct(Translation $translation, $oldLocale = null, $oldTag = null)
    {
        $this->translation = $translation;
        $this->oldLocale = $oldLocale;
        $this->oldTag = $oldTag;
    }
}