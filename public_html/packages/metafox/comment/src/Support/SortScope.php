<?php

namespace MetaFox\Comment\Support;

use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasFeatureSort;
use MetaFox\Platform\Support\Browse\Contracts\HasTotalMemberSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as PlatformSortScope;
use MetaFox\Platform\Support\Browse\Traits\FeatureSortTrait;
use MetaFox\Platform\Support\Browse\Traits\TotalMemberSortTrait;

class SortScope
{
    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_LATEST,
            Browse::SORT_RECENT,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowSortType(): array
    {
        return [
            Browse::SORT_TYPE_DESC,
            Browse::SORT_TYPE_ASC,
        ];
    }
}
