<?php

namespace App\Models;

use App\Models\Concerns\RefreshesStoreCaches;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use RefreshesStoreCaches, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Ikon sprite yang mewakili kategori (dicocokkan dari nama/slug).
     */
    public function icon(): string
    {
        $key = strtolower($this->slug.' '.$this->name);

        return match (true) {
            str_contains($key, 'diecast'), str_contains($key, 'miniatur') => 'i-car',
            str_contains($key, 'figure') => 'i-figure',
            str_contains($key, 'audio'), str_contains($key, 'headset') => 'i-headset',
            str_contains($key, 'keyboard'), str_contains($key, 'mouse') => 'i-keyboard',
            str_contains($key, 'monitor'), str_contains($key, 'display') => 'i-monitor',
            str_contains($key, 'komponen'), str_contains($key, 'pc') => 'i-cpu',
            str_contains($key, 'setup'), str_contains($key, 'kursi') => 'i-chair',
            default => 'i-gamepad',
        };
    }
}
