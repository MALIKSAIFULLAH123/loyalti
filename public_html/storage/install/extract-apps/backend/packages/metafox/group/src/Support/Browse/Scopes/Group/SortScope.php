<?php

namespace MetaFox\Group\Support\Browse\Scopes\Group;

use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasFeatureSort;
use MetaFox\Platform\Support\Browse\Contracts\HasTotalMemberSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as PlatformSortScope;
use MetaFox\Platform\Support\Browse\Traits\FeatureSortTrait;
use MetaFox\Platform\Support\Browse\Traits\TotalMemberSortTrait;

/**
 * Class SortScope.
 */
class SortScope extends PlatformSortScope implements HasTotalMemberSort, HasFeatureSort
{
    use TotalMemberSortTrait;
    use FeatureSortTrait;

    public const SORT_TOTAL_MEMBER_COLUMN = 'total_member';
    public const SORT_FEATURE_COLUMN = 'is_feature';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_LATEST,
            Browse::SORT_RECENT,
            self::SORT_FEATURE,
            self::SORT_MOST_MEMBER,
        ];
    }

    public function getTotalMemberSortColumn(): string
    {
        return self::SORT_TOTAL_MEMBER_COLUMN;
    }

    public function getFeatureSortColumn(): string
    {
        return self::SORT_FEATURE_COLUMN;
    }
}
