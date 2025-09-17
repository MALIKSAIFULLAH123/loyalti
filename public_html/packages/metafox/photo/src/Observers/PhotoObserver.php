<?php

namespace MetaFox\Photo\Observers;

use Exception;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Traits\Album\AlbumTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PhotoObserver
{
    use AlbumTrait {
        increasePhotoAlbumTotal as increasePhotoAlbumTotalMain;
    }

    /**
     * @return AlbumRepositoryInterface
     */
    private function albumRepository(): AlbumRepositoryInterface
    {
        return resolve(AlbumRepositoryInterface::class);
    }

    public function creating(Photo $photo): void
    {
        $this->updateAlbumType($photo);
    }

    public function created(Photo $photo): void
    {
    }

    public function updating(Photo $photo): void
    {
        $this->updateAlbumType($photo);
    }

    public function updated(Photo $photo): void
    {
        if ($photo->isDirty(['is_approved'])) {
            $this->increasePhotoGroupTotal($photo);
            $this->increasePhotoAlbumTotal($photo);
            if ($photo->album_id > 0) {
                $this->albumRepository()->updateAlbumCover($photo->album, $photo->entityId());
            }
        }
    }

    /**
     * @throws Exception
     */
    public function deleted(Photo $photo): void
    {
        resolve(PhotoRepositoryInterface::class)->cleanUpRelationData($photo);

        if ($photo->group_id > 0) {
            resolve(PhotoGroupRepositoryInterface::class)->updateApprovedStatus($photo?->group);
        }
    }

    /**
     * @param Photo $photo
     */
    private function updateAlbumType(Photo $photo): void
    {
        if ($photo->album_id > 0) {
            $album = $photo->album;

            if (null !== $album) {
                $photo->album_type = $album->album_type;
            }
        }
    }

    /**
     * @param Photo $photo
     *
     * @return void
     */
    protected function increasePhotoGroupTotal(Photo $photo): void
    {
        if (!$photo->isApproved()) {
            return;
        }

        if (null === $photo?->group) {
            return;
        }

        if ($photo->isDirty(['is_approved'])) {
            app('events')->dispatch('photo.group.update_media_statistic', [$photo], true);
            app('events')->dispatch('photo.group.increase_total_item', [$photo->group, $photo->entityType()], true);
        }
    }

    protected function increasePhotoAlbumTotal(Photo $item): void
    {
        if (!$this->shouldIncreasePhotoAlbumTotal($item)) {
            return;
        }

        $this->increasePhotoAlbumTotalMain($item);

        $album = $item->album;

        $album->update(['total_photo' => $album->photos()->where('is_approved', 1)->count()]);
    }
}
