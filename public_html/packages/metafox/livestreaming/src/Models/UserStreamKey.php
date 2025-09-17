<?php

namespace MetaFox\LiveStreaming\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\LiveStreaming\Database\Factories\UserStreamKeyFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class UserStreamKey.
 *
 * @property        int                  $id
 * @property        int                  $user_id
 * @property        string               $user_type
 * @property        string               $asset_id
 * @property        string               $stream_key
 * @property        string               $live_stream_id
 * @property        string               $playback_ids
 * @property        int                  $is_streaming
 * @property        int                  $connected_from
 * @method   static UserStreamKeyFactory factory(...$parameters)
 */
class UserStreamKey extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'livestreaming_user_stream_key';

    protected $table = 'livestreaming_user_stream_key';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'asset_id',
        'stream_key',
        'live_stream_id',
        'playback_ids',
        'is_streaming',
        'connected_from',
        'created_at',
        'updated_at',
    ];

    /**
     * @return UserStreamKeyFactory
     */
    protected static function newFactory()
    {
        return UserStreamKeyFactory::new();
    }
}

// end
