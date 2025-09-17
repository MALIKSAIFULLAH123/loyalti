<?php

namespace MetaFox\Music\Http\Resources\v1\Music;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Support\Facades\Music;
use MetaFox\Platform\Support\Browse\Browse;

class SearchMusicForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/music/search')
            ->acceptPageParams(['q', 'sort', 'when', 'genre_id', 'returnUrl', 'entity_type', 'is_featured'])
            ->setValue([
                'view'        => Browse::VIEW_SEARCH,
                'entity_type' => Music::getDefaultSearchEntityType(),
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('music::phrase.search_items'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['view', 'genre_id', 'q']),
            Builder::choice('entity_type')
                ->label(__p('music::phrase.browse_by'))
                ->options($this->getEntityTypes())
                ->marginNormal()
                ->sizeLarge(),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->marginNormal()
                ->sizeLarge()
                ->relatedFieldName('entity_type')
                ->optionRelatedMapping([
                    Music::convertEntityType(Song::ENTITY_TYPE)     => Music::getSongSortOptions(),
                    Music::convertEntityType(Album::ENTITY_TYPE)    => Music::getDefaultSortOptions(),
                    Music::convertEntityType(Playlist::ENTITY_TYPE) => Music::getDefaultSortOptions(),
                ])
                ->options(Music::getDefaultSortOptions()),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->sizeLarge()
                ->options($this->getWhenOptions()),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured'))
                ->showWhen(['neq', 'entity_type', Music::convertEntityType(Playlist::ENTITY_TYPE)]),
            Builder::filterCategory('genre_id')
                ->label(__p('music::phrase.genres'))
                ->apiUrl('/music-genre')
                ->showWhen(['neq', 'entity_type', Music::convertEntityType(Playlist::ENTITY_TYPE)])
                ->marginNormal()
                ->sizeLarge(),
        );
    }

    protected function getSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_RECENT],
            ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED],
            ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED],
        ];
    }

    protected function getWhenOptions(): array
    {
        return [
            ['label' => __p('core::phrase.when.all'), 'value' => Browse::WHEN_ALL],
            ['label' => __p('core::phrase.when.this_month'), 'value' => Browse::WHEN_THIS_MONTH],
            ['label' => __p('core::phrase.when.this_week'), 'value' => Browse::WHEN_THIS_WEEK],
            ['label' => __p('core::phrase.when.today'), 'value' => Browse::WHEN_TODAY],
        ];
    }

    protected function getEntityTypes(): array
    {
        return Music::getEntityTypeOptions();
    }
}
