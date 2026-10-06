<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Pertanyaan asli dari user + versi ternormalisasi untuk pencocokan cache.
            $table->string('question', 500);
            $table->string('question_hash', 64)->unique();

            // Jawaban dari AI (disimpan sebagai cache).
            $table->text('answer');

            // true kalau jawaban ini dihasilkan AI, false kalau hasil dari cache.
            $table->boolean('from_ai')->default(true);

            // Berapa kali jawaban ini dipakai ulang dari cache (statistik hemat token).
            $table->unsignedInteger('hit_count')->default(0);

            // Kapan cache ini kedaluwarsa (TTL).
            $table->timestamp('expires_at')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
