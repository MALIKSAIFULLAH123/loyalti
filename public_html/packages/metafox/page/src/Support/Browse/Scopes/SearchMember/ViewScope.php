<?php

namespace MetaFox\Page\Support\Browse\Scopes\SearchMember;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Models\Block;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_ADMIN        = 'admin';
    public const VIEW_FRIEND       = 'friend';
    public const VIEW_ALL          = 'all';
    public const VIEW_BLOCK        = Block::ENTITY_TYPE;
    public const VIEW_INVITE       = PageInvite::ENTITY_TYPE;

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::VIEW_ADMIN,
            self::VIEW_ALL,
            self::VIEW_FRIEND,
            self::VIEW_BLOCK,
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
