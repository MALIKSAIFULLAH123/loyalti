<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Contracts\User;

class ModelApprovedListener
{
    /**
     * @param User|null $context
     * @param Model     $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $context, Model $model): void
    {
        if ($model instanceof Media) {
            $this->handleUpdateMedia($model);
        }
    }

    private function handleUpdateMedia(Media $model): void
    {
        if (!$model instanceof Model) {
            return;
        }

        $this->updatePhotoGroupStatus($model);
        $this->handleUpdateAlbumItemStatus($model);
    }

    protected function handleUpdateAlbumItemStatus(Media $model): void
    {
        if (!$model->albumItem instanceof AlbumItem) {
            return;
        }

        $model->albumItem->update(['is_approved' => 1]);
    }

    private function updatePhotoGroupStatus(Media $model): void
    {
        if (!$model->group_id) {
            return;
        }

        $this->repository()->updateApprovedStatus($model?->group);
    }

    protected function repository(): PhotoGroupRepositoryInterface
    {
        return resolve(PhotoGroupRepositoryInterface::class);
    }
}
