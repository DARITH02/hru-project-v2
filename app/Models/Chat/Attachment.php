<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = ['message_id', 'file_name', 'file_path', 'file_size', 'mime_type'];

    protected $appends = ['url'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
