<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramBot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bot_token',
        'chat_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
