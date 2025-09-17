<?php

namespace MetaFox\Menu\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use MetaFox\Menu\Database\Factories\MenuFactory;
use MetaFox\Menu\Database\Factories\MenuItemFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Menu.
 *
 * @property        int         $id
 * @property        string      $module_id
 * @property        string      $package_id
 * @property        string      $name
 * @property        string      $title
 * @property        string      $description
 * @property        string      $type
 * @property        string      $filename
 * @property        string      $resource_name
 * @property        int         $is_active
 * @property        int         $is_mobile
 * @property        int         $is_admin
 * @property        int         $version
 * @property        string      $resolution
 * @method   static MenuFactory factory(...$parameters)
 * @method          Builder     isPackageMenus()
 * @method          Builder     isResourceMenus()
 */
class Menu extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'menu';

    protected $table = 'core_menus';

    /** @var string[] */
    protected $fillable = [
        'module_id',
        'package_id',
        'name',
        'type',
        'title',
        'description',
        'resolution',
        'extra',
        'is_active',
        'resource_name',
    ];

    /** @var string[] */
    protected $casts = [
        'extra' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($menu) {
            if ($menu->resource_name === '') {
                $menu->resource_name = null;
            }
        });
    }

    /**
     * @return MenuFactory
     */
    protected static function newFactory(): MenuFactory
    {
        return MenuFactory::new();
    }

    public function getTitleAttribute(?string $value): string
    {
        $title = $value ? app('phrases')->translationOf($value) : $value;

        return $title ?? '';
    }
}

// end
