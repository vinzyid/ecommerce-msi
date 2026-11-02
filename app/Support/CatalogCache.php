<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Cache data katalog yang jarang berubah. Setiap kueri ke database cloud butuh sekitar 100 ms,
 * jadi halaman dengan banyak kueri terasa lambat bila selalu diambil ulang.
 */
class CatalogCache
{
    private const TTL_MINUTES = 10;

    public static function remember(string $key, Closure $callback): mixed
    {
        return Cache::remember(
            'catalog.'.Cache::get('catalog.version', 0).'.'.$key,
            now()->addMinutes(self::TTL_MINUTES),
            $callback
        );
    }

    /**
     * Ganti versi kunci setelah transaksi selesai; entri lama hilang sendiri saat TTL habis.
     */
    public static function flush(): void
    {
        DB::afterCommit(fn () => Cache::forever('catalog.version', uniqid('', true)));
    }
}
