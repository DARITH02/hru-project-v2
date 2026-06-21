<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageEditHistory extends Model
{
    protected $fillable = ['message_id', 'edited_by', 'old_message', 'new_message'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
