<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Platform\Contracts\Media;

class ModelCreatedListener
{
    /**
     * @param  mixed $model
     * @return void
     */
    public function handle($model): void
    {
        if ($model instanceof Media) {
            $this->handleCreateMedia($model);
        }
    }

    private function handleCreateMedia(Media $model): void
    {
        $this->createPhotoGroupItem($model);
        $this->createAlbumItem($model);
    }

    protected function createPhotoGroupItem(Media $model): void
    {
        if ($model->group_id <= 0) {
            return;
        }

        $itemData = [
            'group_id'    => $model->group_id,
            'item_type'   => $model->entityType(),
            'item_id'     => $model->entityId(),
            'ordering'    => 0, //@todo: should be removed?
            'is_approved' => $model->isApproved(),
        ];

        $groupItem = new PhotoGroupItem();
        $groupItem->fill($itemData);
        $groupItem->save();
    }

    protected function createAlbumItem(Media $model): void
    {
        if ($model->album_id <= 0) {
            return;
        }

        $itemData = [
            'album_id'  => $model->album_id,
            'group_id'  => $model->group_id ?? 0,
            'item_type' => $model->entityType(),
            'item_id'   => $model->entityId(),
            'ordering'  => 0, //@todo: should be removed?
            'is_approved' => $model->isApproved(),
        ];

        $groupItem = new AlbumItem();
        $groupItem->fill($itemData);
        $groupItem->save();
    }
}
