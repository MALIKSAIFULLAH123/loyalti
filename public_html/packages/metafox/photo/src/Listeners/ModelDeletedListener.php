<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Support\Facades\Auth;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Photo\Support\Facades\Album;
use MetaFox\Photo\Support\Facades\Photo;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Contracts\User;

class ModelDeletedListener
{
    /**
     * @param  mixed $model
     * @return void
     */
    public function handle($model): void
    {
        if ($model instanceof Media) {
            $this->handleDeleteMedia($model);
        }
    }

    private function handleDeleteMedia(Media $model): void
    {
        if ($model->group_id > 0) {
            $item = PhotoGroupItem::query()
                ->where('item_id', $model->entityId())
                ->where('item_type', $model->entityType())
                ->first();

            if ($item instanceof PhotoGroupItem) {
                $item->delete();
            }

            // Update group status
            $groupRepository = resolve(PhotoGroupRepositoryInterface::class);
            $groupRepository->updateApprovedStatus($model?->group);
            $groupRepository->cleanUpGroup($model?->group);
        }

        if ($model->album_id > 0) {
            $item = AlbumItem::query()
                ->with(['album'])
                ->where('item_id', $model->entityId())
                ->where('item_type', $model->entityType())
                ->first();
            if ($item instanceof AlbumItem) {
                $item->delete();
            }
        }
    }
}
