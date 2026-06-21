<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_approved',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function canUseChat(): bool
    {
        return $this->is_approved && in_array($this->role, ['teacher', 'admin', 'super_admin'], true);
    }

    public function canChatWith(User $user): bool
    {
        if (!$this->canUseChat() || !$user->canUseChat() || $this->id === $user->id) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->role === 'admin') {
            return in_array($user->role, ['teacher', 'super_admin'], true);
        }

        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function chatConversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['last_read_at', 'last_read_message_id', 'muted_until'])
            ->withTimestamps();
    }

    public function chatMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
