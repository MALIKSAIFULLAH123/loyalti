<?php

namespace MetaFox\Menu\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Menu\Database\Factories\MenuItemFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class MenuItem.
 *
 * @property int     $id
 * @property int     $is_active
 * @property string  $menu
 * @property string  $resolution
 * @property string  $parent_name
 * @property string  $name
 * @property ?string $label
 * @property string  $ordering
 * @property string  $created_at
 * @property string  $updated_at
 * @property ?array  $items
 * @property string  $module_id
 * @property string  $package_id
 * @property ?string $to
 * @property ?string $note
 * @property ?string $icon
 * @property ?string $testid
 * @property ?string $value
 * @property ?string $as
 * @property array   $extra
 * @property bool    $is_custom
 * @property ?string $icon_color
 *
 * @method static MenuItemFactory factory(...$parameters)
 */
class MenuItem extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'menuitem';

    protected $table = 'core_menu_items';

    /** @var string[] */
    protected $fillable = [
        'module_id',
        'package_id',
        'menu',
        'parent_name',
        'name',
        'label',
        'note',
        'ordering',
        'is_active',
        'resolution',
        'as',
        'icon',
        'testid',
        'value',
        'to',
        'extra',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'extra' => 'array',
    ];

    protected $appends = ['is_custom'];

    protected static function booted()
    {
        static::saving(function (self $item) {
            if (!$item->parent_name) {
                $item->parent_name = '';
            }
        });
    }

    /**
     * @return MenuItemFactory
     */
    protected static function newFactory(): MenuItemFactory
    {
        return MenuItemFactory::new();
    }

    public function getIsCustomAttribute(): bool
    {
        $extra = $this->extra;

        return Arr::has($extra, 'is_custom') && $extra['is_custom'];
    }

    public function getIconColorAttribute(): ?string
    {
        return Arr::get($this->extra, 'iconColor');
    }

    public function getLabelAttribute($value): ?string
    {
        return __p($value);
    }

    public function getLabelVarAttribute(): ?string
    {
        return Arr::get($this->attributes, 'label');
    }
}

// end
