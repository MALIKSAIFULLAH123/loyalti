<?php

namespace MetaFox\Forum\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Database\Factories\ForumThreadSubscribeFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

class ForumThreadSubscribe extends Model
{
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'forum_thread_subscribe';

    protected $fillable = [
        'item_id',
        'user_id',
        'user_type',
    ];

    protected $table = 'forum_thread_subscribes';


    /**
     * @return ForumThreadSubscribeFactory
     */
    protected static function newFactory()
    {
        return ForumThreadSubscribeFactory::new();
    }
}
