<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'channel_id',
        'author',
        'text',
        'timestamp',
        'timestamp_milli',
        'subtype',
        'reactions',
        'link_previews',
        'is_following',
        'thread_root_info',
        'thread_reply_info',
        'files',
        'deleted',
        'edited',
        'local_id',
        'attachments',
        'saved_timestamp_milli',
        'blocks',
        'meta',
        'author_app_id',
        'system_message',
    ];

    protected $casts = [
        'reactions' => 'json',
        'link_previews' => 'json',
        'is_following' => 'boolean',
        'thread_root_info' => 'json',
        'thread_reply_info' => 'json',
        'files' => 'json',
        'deleted' => 'boolean',
        'edited' => 'boolean',
        'attachments' => 'json',
        'blocks' => 'json',
        'meta' => 'json',
        'system_message' => 'boolean',
        'timestamp' => 'datetime',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'author');
    }
}
