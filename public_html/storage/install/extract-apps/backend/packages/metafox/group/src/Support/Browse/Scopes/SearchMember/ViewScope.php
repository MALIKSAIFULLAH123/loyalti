<?php

namespace MetaFox\Group\Support\Browse\Scopes\SearchMember;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Block;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Models\Mute;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_ADMIN        = 'admin';
    public const VIEW_MODERATOR    = 'moderator';
    public const VIEW_ALL          = 'all';
    public const VIEW_BLOCK        = Block::ENTITY_TYPE;
    public const VIEW_INVITE       = Invite::ENTITY_TYPE;
    public const VIEW_MUTE         = Mute::ENTITY_TYPE;

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::VIEW_ADMIN,
            self::VIEW_MODERATOR,
            self::VIEW_ALL,
            self::VIEW_BLOCK,
            self::VIEW_MUTE,
            self::VIEW_INVITE,
        ];
    }
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
    }
}
