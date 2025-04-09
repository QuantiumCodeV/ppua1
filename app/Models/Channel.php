<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'channel_type',
        'creator_id',
        'workspace_id',
        'is_member',
        'is_muted',
        'is_hidden',
        'is_archived',
        'is_main',
        'is_initial',
        'section_id',
        'last_message_timestamp',
        'last_message_timestamp_milli',
    ];

    protected $casts = [
        'is_member' => 'boolean',
        'is_muted' => 'boolean',
        'is_hidden' => 'boolean',
        'is_archived' => 'boolean',
        'is_main' => 'boolean',
        'is_initial' => 'boolean',
        'last_message_timestamp' => 'datetime',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
