<?php

namespace MetaFox\Music\Http\Resources\v1\Album;

use MetaFox\Music\Http\Resources\v1\Music\SearchMusicMobileForm;

class SearchMobileForm extends SearchMusicMobileForm
{
    protected function getSearchFieldPlaceholder(): string
    {
        return __p('music::phrase.search_albums');
    }
}
