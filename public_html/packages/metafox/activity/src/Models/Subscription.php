<?php

namespace MetaFox\Activity\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Subscription.
 * @mixin Builder
 * @property int  $id
 * @property int  $owner_id
 * @property int  $user_id
 * @property bool $is_active
 */
class Subscription extends Model
{
    public const ENTITY_TYPE = 'activity_subscription';

    protected $table = 'activity_subscriptions';

    protected $fillable = ['owner_id', 'user_id', 'is_active', 'special_type'];

    public $timestamps = false;
}
