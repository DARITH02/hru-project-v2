<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageDeletion extends Model
{
    public $timestamps = false;

    protected $fillable = ['message_id', 'user_id', 'deleted_at'];

    protected function casts(): array
    {
        return ['deleted_at' => 'datetime'];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
