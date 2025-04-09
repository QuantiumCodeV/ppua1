<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    // Указываем, что первичный ключ - UUID
    protected $keyType = 'string';
    
    // Отключаем автоинкремент
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'name',
        'unique_identifier',
        'avatar',
        'custom_status_definitions',
        'previous_unique_identifiers'
    ];

    protected $casts = [
        'avatar' => 'json',
        'custom_status_definitions' => 'json',
        'previous_unique_identifiers' => 'json',
    ];

    // Задаем UUID перед созданием, если он не задан
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'workspace_users')
            ->withPivot(['role', 'status', 'avatar', 'time_zone_id', 'automatically_time_zone', 
                'title', 'phone', 'is_addon_bot', 'time_format', 'custom_status', 
                'invited_by', 'active_until', 'is_pumble_bot', 'broadcast_warning_shown_ts'])
            ->withTimestamps();
    }

    public function channels()
    {
        return $this->hasMany(Channel::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
