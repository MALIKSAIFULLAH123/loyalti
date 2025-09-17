<?php

namespace MetaFox\LiveStreaming\Models;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class NotificationSetting.
 *
 * @property int $id
 */
class NotificationSetting extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'livestreaming_notification_setting';

    protected $table = 'livestreaming_notification_setting';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
    ];
}
