<?php

namespace MetaFox\Photo\Observers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;

/**
 * Class AlbumObserver.
 */
class AlbumObserver
{
    public function updated(Album $album): void
    {
        $this->updateAlbumItems($album);
    }

    /**
     * @throws Exception
     */
    public function deleted(Album $album): void
    {
        $album->albumText()->delete();

        // Delete all of its items
        $this->deleteAlbumItems($album);

        // Delete all photo set belongs to this album
        $this->deletePhotoGroupByAlbum($album);
    }

    private function updatePrivacy(HasPrivacy $detail, Album $album): void
    {
        if ($detail->privacy != $album->privacy) {
            $detail->privacy = $album->privacy;
        }

        if ($detail instanceof AppendPrivacyList) {
            $album->loadPrivacyListAttribute();
            $list = $album->getPrivacyListAttribute() ?? [];
            $detail->setPrivacyListAttribute($list);
        }

        if ($detail instanceof Model) {
            $detail->save();
        }
    }

    private function updateAlbumItems(Album $album): void
    {
        $album->loadMissing(['groupedItems', 'ungroupedItems']);

        // Update item with an existed photo group
        collect($album->groupedItems)->each(function (AlbumItem $item) use ($album) {
            $group = $item->group;
            if (!$group instanceof PhotoGroup) {
                return false;
            }
            $this->updatePrivacy($group, $album);

            return true;
        });

        // Update item with no photo group
        collect($album->ungroupedItems)->each(function (AlbumItem $item) use ($album) {
            $detail = $item->detail;

            if (!$detail instanceof HasPrivacy) {
                return false;
            }

            $this->updatePrivacy($detail, $album);

            return true;
        });
    }

    protected function deleteAlbumItems(Album $album): void
    {
        $album->items()->get()->collect()->each(function (mixed $albumItem) {
            if (!$albumItem instanceof AlbumItem) {
                return true;
            }

            $media = $albumItem->detail()->first();
            if ($media instanceof Media) {
                $media->delete();
            }
        });
    }

    protected function deletePhotoGroupByAlbum(Album $album): void
    {
        resolve(PhotoGroupRepositoryInterface::class)
            ->getModel()
            ->newModelQuery()
            ->where('album_id', '=', $album->entityId())
            ->lazy()
            ->each(function (mixed $photoGroup) {
                if (!$photoGroup instanceof PhotoGroup) {
                    return true;
                }

                $photoGroup->delete();
            });
    }
}
