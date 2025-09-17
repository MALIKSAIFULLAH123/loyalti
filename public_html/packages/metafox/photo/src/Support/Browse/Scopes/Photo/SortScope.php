<?php

namespace MetaFox\Photo\Support\Browse\Scopes\Photo;

use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasAlphabetSort;
use MetaFox\Platform\Support\Browse\Contracts\HasFeatureSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;
use MetaFox\Platform\Support\Browse\Traits\AlphabetSortTrait;
use MetaFox\Platform\Support\Browse\Traits\FeatureSortTrait;

/**
 * Class SortScope.
 */
class SortScope extends BaseScope implements HasAlphabetSort, HasFeatureSort
{
    use AlphabetSortTrait;
    use FeatureSortTrait;

    public const SORT_ALPHABETICAL_COLUMN = 'title';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_RECENT,
            Browse::SORT_LATEST,
            Browse::SORT_MOST_LIKED,
            Browse::SORT_MOST_VIEWED,
            Browse::SORT_MOST_DISCUSSED,
            Browse::SORT_A_TO_Z,
            Browse::SORT_Z_TO_A,
        ];
    }

    public function getAlphabetSortColumn(): string
    {
        return self::SORT_ALPHABETICAL_COLUMN;
    }

    public function getFeatureSortColumn(): string
    {
        return 'featured_at';
    }
}
