<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ConversationParticipant extends Pivot
{
    protected $table = 'conversation_participants';

    public $incrementing = true;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'last_read_at',
        'last_read_message_id',
        'muted_until',
    ];

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'muted_until' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
