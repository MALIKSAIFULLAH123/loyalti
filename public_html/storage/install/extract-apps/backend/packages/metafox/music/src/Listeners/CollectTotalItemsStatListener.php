<?php

namespace MetaFox\Music\Listeners;

use Carbon\Carbon;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;
use MetaFox\Music\Repositories\PlaylistRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;

class CollectTotalItemsStatListener extends AbstractClass
{
    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => Song::ENTITY_TYPE,
                'label' => 'music::phrase.song_stat_label',
                'value' => resolve(SongRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
            [
                'name'  => Playlist::ENTITY_TYPE,
                'label' => 'music::phrase.playlist_stat_label',
                'value' => resolve(PlaylistRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
            [
                'name'  => Album::ENTITY_TYPE,
                'label' => 'music::phrase.album_stat_label',
                'value' => resolve(AlbumRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
        ];
    }

    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPendingStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => 'pending_song',
                'label' => 'music::phrase.song_stat_label',
                'value' => resolve(SongRepositoryInterface::class)->getTotalPendingItemByPeriod(),
            ],
        ];
    }
}
