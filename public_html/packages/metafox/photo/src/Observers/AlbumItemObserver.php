<?php

namespace MetaFox\Photo\Observers;

use MetaFox\Photo\Contracts\HasTotalPhoto;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasTotalItem;

/**
 * Class AlbumItemObserver.
 */
class AlbumItemObserver
{
    /**
     * @var AlbumRepositoryInterface
     */
    protected $albumRepository;

    public function __construct(AlbumRepositoryInterface $albumRepository)
    {
        $this->albumRepository = $albumRepository;
    }

    private function increaseAmounts(Album $album, string $itemType): void
    {
        if ($album instanceof HasTotalItem) {
            $album->incrementAmount('total_item');
        }

        if (Photo::ENTITY_TYPE == $itemType && $album instanceof HasTotalPhoto) {
            $album->incrementAmount('total_photo');
        }

        $this->updateAlbumGlobalSearch($album);
    }

    private function decreaseAmounts(Album $album, string $itemType): void
    {
        if (Photo::ENTITY_TYPE == $itemType && $album instanceof HasTotalPhoto) {
            $album->decrementAmount('total_photo');
        }

        if ($album instanceof HasTotalItem) {
            $album->decrementAmount('total_item');
        }

        $this->updateAlbumGlobalSearch($album);
    }

    public function created(AlbumItem $albumItem): void
    {
        $item = $albumItem->detail;

        if ($item->isApproved()) {
            $this->increaseAmounts($albumItem->album, $albumItem->item_type);

            if ($item instanceof Photo) {
                $this->albumRepository->updateAlbumCover($albumItem->album, $item->entityId());
            }
        }
    }

    public function deleted(AlbumItem $albumItem): void
    {
        if (null === $albumItem->album) {
            return;
        }

        if ($albumItem->album->cover_photo_id == 0 || $albumItem->album->cover_photo_id == $albumItem->item_id) {
            $this->albumRepository->updateAlbumCover($albumItem->album);
        }

        $this->decreaseAmounts($albumItem->album, $albumItem->item_type);
    }

    public function updated(AlbumItem $albumItem): void
    {
        if ($albumItem->isDirty(['album_id'])) {
            if ($albumItem->album_id > 0) {
                $this->increaseAmounts($albumItem->album, $albumItem->item_type);
            }

            $oldAlbumId = $albumItem->getOriginal('album_id');
            if ($oldAlbumId > 0) {
                $oldAlbum = $this->albumRepository->find($oldAlbumId);
                $this->decreaseAmounts($oldAlbum, $albumItem->item_type);
            }

            if ($albumItem->item_type == Photo::ENTITY_TYPE) {
                $this->updateAlbumCover($albumItem);
            }
        }
    }

    protected function updateAlbumGlobalSearch(Album $album): void
    {
        if (!$album instanceof HasGlobalSearch) {
            return;
        }

        $album->load(['items']);

        $items = $album->items;

        if ($items->count() <= 0) {
            app('events')->dispatch('search.delete_item', [$album->entityType(), $album->entityId()]);

            return;
        }

        app('events')->dispatch('search.update_item', $album);
    }

    /**
     * @param AlbumItem $albumItem
     */
    private function updateAlbumCover(AlbumItem $albumItem): void
    {
        $oldAlbumId = $albumItem->getOriginal('album_id');

        if ($oldAlbumId > 0) {
            $oldAlbum        = $this->albumRepository->find($oldAlbumId);
            $isOldAlbumCover = $oldAlbum->cover_photo_id == $albumItem->item_id;
            if ($isOldAlbumCover) {
                $this->albumRepository->updateAlbumCover($oldAlbum);
            }
        }
    }
}

// end stub
