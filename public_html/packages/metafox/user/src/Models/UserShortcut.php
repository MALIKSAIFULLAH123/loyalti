<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Database\Factories\UserShortcutFactory;

/**
 * Class UserShortcut.
 *
 * @property int    $id
 * @property int    $sort_type
 * @method   static UserShortcutFactory factory(...$parameters)
 * @mixin Builder
 */
class UserShortcut extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasItemMorph;

    public const SORT_DEFAULT = 1;
    public const SORT_PIN     = 2;
    public const SORT_HIDE    = 0;

    public const ENTITY_TYPE = 'shortcut';

    protected $table = 'user_shortcuts';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'sort_type',
        'updated_at',
    ];

    /**
     * @return UserShortcutFactory
     */
    protected static function newFactory(): UserShortcutFactory
    {
        return UserShortcutFactory::new();
    }

    public function toLink()
    {
        return '';
    }
}

// end
