<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    protected $fillable = [
        'photoable_type',
        'photoable_id',
        'photo_type',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'is_primary',
        'uploaded_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'file_size' => 'integer',
    ];

    protected $appends = ['url'];

    protected static function booted(): void
    {
        static::deleting(function (Photo $photo) {
            if ($photo->file_path) {
                Storage::disk('public')->delete($photo->file_path);
            }
        });
    }

    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return '/storage/' . ltrim($this->file_path, '/');
    }
}
