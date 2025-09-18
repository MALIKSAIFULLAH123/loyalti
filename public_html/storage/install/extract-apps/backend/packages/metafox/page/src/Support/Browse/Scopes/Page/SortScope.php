<?php

namespace MetaFox\Page\Support\Browse\Scopes\Page;

use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasTotalMemberSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as PlatformSortScope;
use MetaFox\Platform\Support\Browse\Traits\TotalMemberSortTrait;

/**
 * Class SortScope.
 */
class SortScope extends PlatformSortScope implements HasTotalMemberSort
{
    use TotalMemberSortTrait;

    public const SORT_TOTAL_MEMBER_COLUMN = 'total_member';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_LATEST,
            Browse::SORT_RECENT,
            self::SORT_MOST_MEMBER,
        ];
    }

    public function getTotalMemberSortColumn(): string
    {
        return self::SORT_TOTAL_MEMBER_COLUMN;
    }
}
