<?php

namespace MetaFox\Authorization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Authorization\Database\Factories\UserDeviceFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Device.
 *
 * @property        int         $id
 * @property        string      $device_token
 * @property        string      $device_id
 * @property        string|null $device_uid
 * @property        string      $platform
 * @property        string|null $platform_version
 * @property        int         $is_active
 * @property        string      $token_source
 * @property        string      $token_id
 * @property        array       $extra
 * @property        string      $created_at
 * @property        string      $updated_at
 * @method   static UserDeviceFactory factory(...$parameters)
 */
class UserDevice extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'core_user_device';

    protected $table = 'core_user_devices';

    public const DEVICE_WEB_PLATFORM    = 'web';
    public const DEVICE_MOBILE_PLATFORM = 'mobile';

    /**
     * @var string[]
     */
    protected $casts = [
        'extra' => 'array',
    ];

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'device_token',
        'device_id',
        'device_uid',
        'platform',
        'platform_version',
        'token_source',
        'token_id',
        'is_active',
        'extra',
        'created_at',
        'updated_at',
    ];

    /**
     * @return UserDeviceFactory
     */
    protected static function newFactory()
    {
        return UserDeviceFactory::new();
    }
}

// end
