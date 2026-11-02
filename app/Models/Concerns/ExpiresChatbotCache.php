<?php

namespace App\Models\Concerns;

use App\Models\ChatbotMessage;

/**
 * Untuk model yang datanya dipakai chatbot menjawab: begitu datanya berubah,
 * jawaban tersimpan dianggap basi agar harga & stok yang disebut tetap akurat.
 *
 * Memakai event "updated" (bukan "saved") karena $model->decrement() hanya memicu "updated".
 */
trait ExpiresChatbotCache
{
    protected static function bootExpiresChatbotCache(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(fn () => ChatbotMessage::expireAll());
        }
    }
}
