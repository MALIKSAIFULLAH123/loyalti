<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Facades\LoadReduce;

class ModelUpdatedListener
{
    /**
     * @param  mixed $model
     * @return void
     */
    public function handle($model): void
    {
        if (!$model instanceof Model) {
            return;
        }

        $this->handleUpdateMedia($model);
        $this->handleApprovePhotoGroupOfOwner($model);
    }

    private function handleUpdateMedia(Model $model): void
    {
        if (!$model instanceof Media) {
            return;
        }

        $this->handleUpdateMediaAlbumId($model);
        $this->handleUpdateMediaGroupId($model);
        $this->handleUpdatePhotoGroupProcessingItems($model);
        $this->handleMediaApproved($model);
    }

    private function handleMediaApproved(Media $media): void
    {
        if ($media->wasRecentlyCreated) {
            return;
        }

        if (!$media->group_id) {
            return;
        }

        if (!$media->isDirty(['is_approved'])) {
            return;
        }

        if (null === $media->groupItem) {
            return;
        }

        $media->groupItem->update(['is_approved' => (int) $media->isApproved()]);
    }

    /**
     * This method only handles the approval process from inside User which has pending mode.
     * For case approve Media item from inside its app, see \MetaFox\Photo\Listeners\ModelApprovedListener.
     */
    private function handleApprovePhotoGroupOfOwner(Model $model): void
    {
        if (!$model instanceof PhotoGroup) {
            return;
        }

        $owner = $model->owner;
        if (!$owner->hasPendingMode()) {
            return;
        }

        if (!$model->isApproved()) {
            return;
        }

        $this->updatePhotoGroupItems($model);
    }

    private function handleUpdateMediaAlbumId(Media $model): void
    {
        if (!$model->isDirty('album_id')) {
            return;
        }

        $oldAlbumId = $model->getOriginal('album_id');
        if ($oldAlbumId > 0) {
            $albumItem = $model->albumItem;
            if (!$albumItem instanceof AlbumItem) {
                return;
            }

            if ($model->album_id > 0) {
                $albumItem->album_id = $model->album_id;
                $albumItem->save();
            }

            if ($model->album_id === 0) {
                $albumItem->delete();
            }
        }

        if ($oldAlbumId === 0 && $model->album_id > 0) {
            $this->createAlbumItemForMedia($model);
        }
    }

    private function createAlbumItemForMedia(Media $model): AlbumItem
    {
        $itemData = [
            'album_id'  => $model->album_id,
            'group_id'  => $model->group_id ?? 0,
            'item_type' => $model->entityType(),
            'item_id'   => $model->entityId(),
            'ordering'  => 0, //@todo: should be removed?
        ];

        $albumItem = new AlbumItem();
        $albumItem->fill($itemData);
        $albumItem->save();

        return $albumItem;
    }

    private function updatePhotoGroupItems(PhotoGroup $photoGroup): void
    {
        $pendingItems = $photoGroup->pendingItems()->get()->collect();

        $pendingItems->each(function (PhotoGroupItem $item) {
            $detail = $item->detail;

            // Skip if not Content
            if (!$detail instanceof Content) {
                return true;
            }

            $detail->fill(['is_approved' => 1]);

            $detail->save();
        });
    }

    private function handleUpdatePhotoGroupProcessingItems(Media $media): void
    {
        $photoGroup = $media->group;
        if (!$photoGroup instanceof PhotoGroup) {
            return;
        }

        if ($photoGroup->processingItems()->count()) {
            return;
        }

        LoadReduce::flush();

        $photoGroup->load('activity_feed');

        $fromResource = !$media instanceof Photo ? 'feed' : null;

        $feed = $photoGroup->activity_feed ?? app('events')->dispatch('activity.feed.create_from_resource', [$photoGroup, $fromResource], true);

        if (null === $feed) {
            return;
        }

        app('events')->dispatch('hashtag.create_hashtag', [$photoGroup->user, $feed, $feed->content], true);
    }

    private function handleUpdateMediaGroupId(Media $model): void
    {
        if (!$model->isDirty('group_id')) {
            return;
        }

        $currentGroupID = $model->group_id;
        if ($currentGroupID > 0) {
            if ($model->groupItem instanceof PhotoGroupItem) {
                $model->groupItem?->update(['group_id' => $currentGroupID]);

                return;
            }

            $groupItem = new PhotoGroupItem();
            $groupItem->fill([
                'group_id'  => $model->group_id ?? 0,
                'item_type' => $model->entityType(),
                'item_id'   => $model->entityId(),
                'ordering'  => 0,
            ]);
            $groupItem->save();

            return;
        }

        if ($currentGroupID === 0) {
            $model->groupItem?->delete();
        }
    }
}
