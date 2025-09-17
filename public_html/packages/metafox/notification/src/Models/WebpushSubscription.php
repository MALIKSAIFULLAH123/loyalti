<?php

namespace MetaFox\Notification\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WebpushSubscription.
 * @property int    $id
 * @property int    $subscribable_id
 * @property string $subscribable_type
 * @property string $endpoint
 * @property string $public_key
 * @property string $auth_token
 */
class WebpushSubscription extends Model
{
    protected $table = 'notification_webpush_subscriptions';

    protected $primaryKey = 'id';

    protected $fillable = [
        'subscribable_id',
        'subscribable_type',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];
}
