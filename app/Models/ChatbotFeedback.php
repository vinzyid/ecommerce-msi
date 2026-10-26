<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotFeedback extends Model
{
    protected $table = 'chatbot_feedback';

    protected $fillable = [
        'user_id',
        'chatbot_message_id',
        'question',
        'answer',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chatbotMessage(): BelongsTo
    {
        return $this->belongsTo(ChatbotMessage::class);
    }
}
