<?php

namespace MetaFox\Photo\Listeners;

use Carbon\Carbon;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;

class CollectTotalItemsStatListener extends AbstractClass
{
    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => Album::ENTITY_TYPE,
                'label' => 'photo::phrase.photo_album_stat_label',
                'value' => resolve(AlbumRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
            [
                'name'  => Photo::ENTITY_TYPE,
                'label' => 'photo::phrase.photo_stat_label',
                'value' => resolve(PhotoRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
        ];
    }

    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPendingStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => 'pending_photo',
                'label' => 'photo::phrase.photo_stat_label',
                'value' => resolve(PhotoRepositoryInterface::class)->getTotalPendingItemByPeriod(),
            ],
        ];
    }
}
