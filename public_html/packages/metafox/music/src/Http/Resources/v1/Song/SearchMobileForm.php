<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use MetaFox\Music\Http\Resources\v1\Music\SearchMusicMobileForm;
use MetaFox\Music\Support\Browse\Scopes\Song\SortScope;
use MetaFox\Platform\Support\Browse\Browse;

class SearchMobileForm extends SearchMusicMobileForm
{
    protected function getSearchFieldPlaceholder(): string
    {
        return __p('music::phrase.search_songs');
    }

    protected function getSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_RECENT],
            ['label' => __p('music::phrase.most_played'), 'value' => SortScope::SORT_MOST_PLAYED],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED],
            ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED],
        ];
    }
}
