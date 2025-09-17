<?php

namespace MetaFox\Notification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Notification\Database\Factories\NotificationSettingFactory;

/**
 * Class NotificationSetting.
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $type_id
 * @property string $user_type
 * @property string $channel
 * @property int    $user_value
 * @method static NotificationSettingFactory factory(...$parameters)
 */
class NotificationSetting extends Model
{
    use HasFactory;

    protected $table = 'notification_settings';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_type',
        'type_id',
        'channel',
        'user_value',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'user_value' => 'array',
    ];

    protected static function newFactory(): NotificationSettingFactory
    {
        return NotificationSettingFactory::new();
    }
}
