<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class IntegratedModule.
 *
 * @property int    $id
 * @property int    $group_id
 * @property string $name
 * @property string $label
 * @property string $module_id
 * @property int    $ordering
 * @property int    $is_active
 * @property Group  $group
 */
class IntegratedModule extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'group_integrated_module';

    protected $table = 'group_integrated_modules';
    public const MENU_NAME = 'group.group.profileMenu';
    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'group_id',
        'module_id',
        'package_id',
        'name',
        'tab',
        'menu',
        'label',
        'ordering',
        'is_active',
    ];

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id')->withTrashed();
    }
}

// end
