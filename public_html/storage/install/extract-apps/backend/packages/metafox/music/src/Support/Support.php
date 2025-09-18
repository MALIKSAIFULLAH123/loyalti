<?php

namespace MetaFox\Music\Support;

use MetaFox\Music\Contracts\SupportInterface;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Support\Browse\Scopes\Song\SortScope as SongSortScope;
use MetaFox\Platform\Support\Browse\Browse;

class Support implements SupportInterface
{
    public function getDefaultSearchEntityType(): string
    {
        return $this->convertEntityType(Song::ENTITY_TYPE);
    }

    public function convertEntityType(string $entityType): string
    {
        return str_replace('_', '/', $entityType);
    }

    public function getEntityTypeOptions(): array
    {
        return [
            [
                'value' => $this->convertEntityType(Song::ENTITY_TYPE),
                'label' => __p('music::phrase.song'),
            ],
            [
                'value' => $this->convertEntityType(Album::ENTITY_TYPE),
                'label' => __p('music::phrase.album'),
            ],
            [
                'value' => $this->convertEntityType(Playlist::ENTITY_TYPE),
                'label' => __p('music::phrase.playlist'),
            ],
        ];
    }

    public function getSongSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_RECENT],
            ['label' => __p('music::phrase.most_played'), 'value' => SongSortScope::SORT_MOST_PLAYED],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED],
            ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED],
        ];
    }

    public function getDefaultSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_RECENT],
            ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED],
            ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED],
        ];
    }
}
