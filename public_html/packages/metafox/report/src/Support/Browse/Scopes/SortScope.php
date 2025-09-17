<?php

namespace MetaFox\Report\Support\Browse\Scopes;

use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

class SortScope extends BaseScope
{
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_RECENT,
        ];
    }
}
