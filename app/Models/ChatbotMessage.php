<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'user_id',
        'question',
        'question_hash',
        'answer',
        'from_ai',
        'hit_count',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'from_ai' => 'boolean',
            'hit_count' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Normalisasi pertanyaan agar variasi huruf besar/kecil, tanda baca,
     * dan spasi berlebih tetap dianggap pertanyaan yang sama.
     */
    public static function normalizeQuestion(string $question): string
    {
        $normalized = Str::lower(trim($question));

        // Buang tanda baca, sisakan huruf/angka/spasi.
        $normalized = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $normalized);

        // Rapikan spasi berlebih.
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return trim($normalized);
    }

    /**
     * Hash normalisasi pertanyaan — dipakai sebagai kunci unik cache.
     */
    public static function hashQuestion(string $question): string
    {
        return hash('sha256', self::normalizeQuestion($question));
    }

    /**
     * Apakah jawaban ini masih berlaku (belum kedaluwarsa)?
     */
    public function isFresh(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isFuture();
    }
}
