<?php

namespace MetaFox\Notification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Notification\Database\Factories\ModuleSettingFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class ModuleSetting.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $module_id
 * @property int    $user_value
 * @method   static ModuleSettingFactory factory(...$parameters)
 */
class ModuleSetting extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'module_setting';
    public $timestamps       = false;

    protected $table = 'notification_module_settings';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'module_id',
        'user_value',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'user_value' => 'array',
    ];

    /**
     * @return ModuleSettingFactory
     */
    protected static function newFactory()
    {
        return ModuleSettingFactory::new();
    }
}

// end
