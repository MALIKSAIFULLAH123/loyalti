<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Database\Factories\AdminSearchFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class AdminSearch.
 *
 * @property int    $id
 * @property string $title
 * @property string $caption
 * @property string $group
 * @property string $text
 * @property string $lang
 * @property string $url
 * @property string $package_id
 * @property string $module_id
 *
 *
 * @method static AdminSearchFactory factory(...$parameters)
 */
class AdminSearch extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'admin_search';

    protected $table = 'core_admin_search';

    /** @var string[] */
    protected $fillable = [
        'title',
        'caption',
        'group',
        'text',
        'uid',
        'lang',
        'url',
        'package_id',
    ];

    protected static function booted()
    {
        self::saving(function (self $search) {
            if (!$search->module_id) {
                $search->module_id = PackageManager::getAlias($search->package_id);
            }
        });
    }

    /**
     * @return AdminSearchFactory
     */
    protected static function newFactory()
    {
        return AdminSearchFactory::new();
    }
}

// end
