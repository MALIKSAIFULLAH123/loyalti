<?php

namespace MetaFox\GettingStarted\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\GettingStarted\Models\TodoListImage;
use MetaFox\GettingStarted\Models\TodoListImage as Model;
use MetaFox\GettingStarted\Policies\TodoListPolicy;
use MetaFox\GettingStarted\Repositories\TodoListImageRepositoryInterface;
use MetaFox\GettingStarted\Repositories\TodoListRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class TodoListImageRepository.
 * @property Model $model
 * @method   Model getModel()
 * @method   Model find($id, $columns = ['*'])()
 * @ignore
 * @codeCoverageIgnore
 */
class TodoListImageRepository extends AbstractRepository implements TodoListImageRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    public function updateImages(User $context, int $id, ?array $attachedPhotos, bool $isUpdated = true): bool
    {
        if (null === $attachedPhotos) {
            return false;
        }

        $todoList = resolve(TodoListRepositoryInterface::class)->find($id);

        if ($isUpdated) {
            policy_authorize(TodoListPolicy::class, 'updateAdminCP', $context);
        }

        if (0 === count($attachedPhotos)) {
            $this->getModel()->newModelQuery()
                ->where([
                    'todo_list_id' => $todoList->entityId(),
                ])
                ->get()
                ->each(function ($currentPhoto) {
                    $currentPhoto->delete();
                });

            return true;
        }

        $newPhotos = array_filter($attachedPhotos, function ($attachedPhoto) {
            return $attachedPhoto['status'] == MetaFoxConstant::FILE_NEW_STATUS;
        });

        $removedPhotos = array_filter($attachedPhotos, function ($attachedPhoto) {
            return $attachedPhoto['status'] == MetaFoxConstant::FILE_REMOVE_STATUS;
        });

        $updatedPhotos = array_filter($attachedPhotos, function ($attachedPhoto) {
            return $attachedPhoto['status'] == MetaFoxConstant::FILE_UPDATE_STATUS;
        });

        /*
         * Must remove first to update ordering before creating
         */
        $this->removePhotos($removedPhotos);

        $this->createPhotos($id, $newPhotos);
        $this->updatePhotos($updatedPhotos);

        return true;
    }

    protected function createPhotos(int $todoListId, array $newPhotos): void
    {
        if (!count($newPhotos)) {
            return;
        }

        $defaultOrdering = $this->getNextOrdering($todoListId);

        foreach ($newPhotos as $newPhoto) {
            $tempFileId  = Arr::get($newPhoto, 'temp_file');
            $newOrdering = Arr::get($newPhoto, 'ordering');

            if (!$newOrdering) {
                $newOrdering = $defaultOrdering;
            }

            if (!$tempFileId) {
                continue;
            }

            $tempFile = upload()->getFile($tempFileId);

            $model = new TodoListImage();

            $model->fill([
                'todo_list_id'  => $todoListId,
                'image_file_id' => $tempFile->entityId(),
                'ordering'      => (int) $newOrdering,
            ]);

            $success = $model->save();

            if ($success) {
                $defaultOrdering++;
            }

            upload()->rollUp($tempFileId);
        }
    }

    protected function getNextOrdering(int $todoListId): int
    {
        $lastPhoto = $this->getModel()->newModelQuery()
            ->where([
                'todo_list_id' => $todoListId,
            ])
            ->orderByDesc('ordering')
            ->first();

        if (null === $lastPhoto) {
            return 1;
        }

        return (int) $lastPhoto->ordering + 1;
    }
    protected function updatePhotos(array $updatePhotos): void
    {
        if (!count($updatePhotos)) {
            return;
        }

        foreach ($updatePhotos as $photo) {
            $id          = Arr::get($photo, 'id');
            $newOrdering = Arr::get($photo, 'ordering', $photo?->ordering ?? 0);

            if (!$id) {
                continue;
            }

            $this->getModel()->newModelQuery()
                ->where('id', $id)
                ->update([
                    'ordering' => $newOrdering,
                ]);
        }
    }

    protected function removePhotos(array $removedPhotos): void
    {
        if (!count($removedPhotos)) {
            return;
        }

        $photoIds = Arr::pluck($removedPhotos, 'id');

        if (!count($photoIds)) {
            return;
        }

        $photos = $this->getModel()->newModelQuery()
            ->whereIn('id', $photoIds)
            ->get();

        if (0 === $photos->count()) {
            return;
        }

        foreach ($photos as $photo) {
            $photo->delete();
        }
    }
}
