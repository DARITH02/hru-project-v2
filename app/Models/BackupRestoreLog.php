<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupRestoreLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'file_name',
        'storage_disk',
        'backup_size',
        'status',
        'message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'backup_size' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
