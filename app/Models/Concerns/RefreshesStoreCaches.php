<?php

namespace App\Models\Concerns;

use App\Models\ChatbotMessage;
use App\Support\CatalogCache;

/**
 * Untuk model yang datanya dipakai chatbot dan katalog: begitu datanya berubah,
 * jawaban chatbot dan data katalog tersimpan dianggap basi agar harga & stok tetap akurat.
 *
 * Memakai event "updated" (bukan "saved") karena $model->decrement() hanya memicu "updated".
 */
trait RefreshesStoreCaches
{
    protected static function bootRefreshesStoreCaches(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function () {
                ChatbotMessage::expireAll();
                CatalogCache::flush();
            });
        }
    }
}
