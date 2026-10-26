<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Entri cache yang dinilai (kosong untuk jawaban lanjutan yang tidak di-cache).
            $table->foreignId('chatbot_message_id')->nullable()->constrained()->nullOnDelete();

            // Salinan pertanyaan & jawaban agar tetap terbaca walau cache sudah dihapus.
            $table->string('question', 500);
            $table->text('answer');

            // 1 = membantu (👍), -1 = tidak membantu (👎).
            $table->smallInteger('rating')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_feedback');
    }
};
