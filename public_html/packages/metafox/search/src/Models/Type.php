<?php

namespace MetaFox\Search\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsBitwiseFlagInterface;
use MetaFox\Platform\Traits\Eloquent\Model\BitwiseFlag;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Search\Database\Factories\SearchFactory;
use MetaFox\Search\Database\Factories\TypeFactory;

/**
 * Class Type.
 *
 * @mixin Builder
 * @property int    $id
 * @property string $type
 * @property string $module_id
 * @property string $entity_type
 * @property string $title
 * @property string $description
 * @property bool   $is_active
 * @property int    $system_value
 * @property bool   $is_system
 * @property bool   $can_search_feed
 * @property array  $params
 *
 * @method SearchFactory factory(...$parameters)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 */
class Type extends Model implements Entity, IsBitwiseFlagInterface
{
    use HasEntity;
    use BitwiseFlag;
    use HasFactory;

    public const ENTITY_TYPE = 'search_type';

    public const CAN_SEARCH_FEED = 1;

    public const CAN_SEARCH_FEED_TYPE = 'can_search_feed_type';

    protected $table = 'search_types';

    /**
     * @var string[]
     */
    protected $fillable = [
        'type', 'module_id', 'entity_type', 'title', 'description', 'is_active', 'is_system',
        'params',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'params' => 'array',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $appends = [
        'can_search_feed',
    ];

    protected static function newFactory(): TypeFactory
    {
        return TypeFactory::new();
    }

    public function getFlagName(): string
    {
        return 'system_value';
    }

    /**
     * @return array<string, int>
     */
    public function getAbilities(): array
    {
        return [
            self::CAN_SEARCH_FEED_TYPE => self::CAN_SEARCH_FEED,
        ];
    }

    /**
     * @param bool $ability
     *
     * @return self
     */
    public function setCanSearchFeedAttribute(bool $ability = true): self
    {
        return $this->setFlag(self::CAN_SEARCH_FEED, $ability);
    }

    /**
     * @return bool
     */
    public function getCanSearchFeedAttribute(): bool
    {
        return $this->getFlag(self::CAN_SEARCH_FEED);
    }
}
