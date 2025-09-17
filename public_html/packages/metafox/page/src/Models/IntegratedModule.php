<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property int    $page_id
 * @property string $name
 * @property string $label
 * @property string $tab
 * @property string $module_id
 * @property int    $ordering
 * @property int    $is_active
 * @property Page   $page
 */
class IntegratedModule extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'page_integrated_module';

    protected $table = 'page_integrated_modules';

    public const TAB_NAME_DEFAULTS = ['home', 'about', 'members', 'photo'];

    public $timestamps = false;

    public const MENU_NAME = 'page.page.profileMenu';
    /** @var string[] */
    protected $fillable = [
        'page_id',
        'module_id',
        'package_id',
        'name',
        'menu',
        'label',
        'tab',
        'ordering',
        'is_active',
    ];

    public function page(): HasOne
    {
        return $this->hasOne(Page::class, 'id', 'page_id')->withTrashed();
    }
}

// end
