<?php

namespace App\Models;

use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class Review extends Model
{
    use HasFactory;

    private const PHOTO_DIR = 'images/reviews';

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
        'photo_path',
        'is_approved',
    ];

    protected static function booted(): void
    {
        // Rating di kartu produk ikut dari ulasan.
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(fn () => CatalogCache::flush());
        }

        // File foto ikut dibersihkan saat diganti atau ulasannya dihapus.
        static::updated(function (Review $review) {
            if ($review->wasChanged('photo_path')) {
                self::deletePhoto($review->getOriginal('photo_path'));
            }
        });
        static::deleted(fn (Review $review) => self::deletePhoto($review->photo_path));
    }

    public static function storePhoto(UploadedFile $file): string
    {
        $name = $file->hashName();
        $file->move(public_path(self::PHOTO_DIR), $name);

        return '/'.self::PHOTO_DIR.'/'.$name;
    }

    private static function deletePhoto(?string $path): void
    {
        if ($path && str_starts_with($path, '/'.self::PHOTO_DIR.'/')) {
            File::delete(public_path(ltrim($path, '/')));
        }
    }

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
