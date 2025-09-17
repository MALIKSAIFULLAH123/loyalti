<?php

namespace MetaFox\Activity\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Stream.
 * @mixin Builder
 * @property int    $id
 * @property int    $feed_id
 * @property int    $user_id
 * @property int    $owner_id
 * @property string $owner_type
 * @property int    $item_id
 * @property string $item_type
 * @property int    $privacy_id
 * @property int    $status
 * @property string $created_at
 * @property string $updated_at
 */
class Stream extends Model
{
    public const ENTITY_TYPE = 'activity_stream';

    protected $table = 'activity_streams';

    public const STATUS_ALLOW = 1;

    protected $fillable       = [
        'feed_id', 'user_id', 'owner_id', 'owner_type', 'item_id', 'item_type', 'privacy_id', 'status', 'created_at',
        'updated_at',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Feed::class, 'feed_id', 'id');
    }
}
