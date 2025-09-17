<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use MetaFox\Music\Http\Resources\v1\Music\SearchMusicMobileForm;

class SearchMobileForm extends SearchMusicMobileForm
{
    protected function getSearchFieldPlaceholder(): string
    {
        return __p('music::phrase.search_playlists');
    }

    protected function getBasicFieldFeatured(): ?\MetaFox\Form\Mobile\SwitchField
    {
        return null;
    }

    protected function getBottomSheetFieldFeatured(): ?\MetaFox\Form\Mobile\SwitchField
    {
        return null;
    }

    protected function getBasicFieldGenres(): ?\MetaFox\Form\Mobile\Autocomplete
    {
        return null;
    }

    protected function getBottomSheetFieldGenres(): ?\MetaFox\Form\Mobile\Autocomplete
    {
        return null;
    }

}
