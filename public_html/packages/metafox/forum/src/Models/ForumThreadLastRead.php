<?php

namespace MetaFox\Forum\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Database\Factories\ForumThreadLastReadFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

class ForumThreadLastRead extends Model implements Entity
{
    use HasFactory;
    use HasEntity;

    public const ENTITY_TYPE = 'forum_thread_last_read';

    protected $fillable = [
        'user_id',
        'user_type',
        'thread_id',
        'post_id',
        'created_at',
        'updated_at',
    ];

    protected $table = 'forum_thread_last_read';

    public $timestamps = false;

    /**
     * @return ForumThreadLastReadFactory
     */
    protected static function newFactory()
    {
        return ForumThreadLastReadFactory::new();
    }
}
