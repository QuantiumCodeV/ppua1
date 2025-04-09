<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'time_zone_id',
        'automatically_time_zone',
        'title',
        'phone',
        'time_format',
        'avatar',
        'is_addon_bot',
        'is_pumble_bot',
        'workspace_id',
        'role',
        'status',
        'custom_status',
        'invited_by',
        'active_until',
        'broadcast_warning_shown_ts',
        'verification_code',
        'email_verified_at',
        'terms_accepted'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'automatically_time_zone' => 'boolean',
        'is_addon_bot' => 'boolean',
        'is_pumble_bot' => 'boolean',
        'avatar' => 'json',
        'custom_status' => 'json',
        'active_until' => 'integer',
    ];

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'workspace_users')
            ->withPivot(['role', 'status', 'avatar', 'time_zone_id', 'automatically_time_zone', 
                'title', 'phone', 'is_addon_bot', 'time_format', 'custom_status', 
                'invited_by', 'active_until', 'is_pumble_bot', 'broadcast_warning_shown_ts'])
            ->withTimestamps();
    }

    public function channels()
    {
        return $this->hasMany(Channel::class, 'creator_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'author');
    }
}
