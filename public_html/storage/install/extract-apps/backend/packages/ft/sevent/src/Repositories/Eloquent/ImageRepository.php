<?php

namespace Foxexpert\Sevent\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Foxexpert\Sevent\Models\Image;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Policies\SeventPolicy;
use Foxexpert\Sevent\Repositories\ImageRepositoryInterface;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class ImageRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class ImageRepository extends AbstractRepository implements ImageRepositoryInterface
{
    public function model(): string
    {
        return Image::class;
    }

    /**
     * @param  User                                           $context
     * @param  int                                            $id
     * @param  array|null                                     $attachedPhotos
     * @param  bool                                           $isCreated
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateImages(User $context, int $id, ?array $attachedPhotos, bool $isUpdated = true): bool
    {
        if (null === $attachedPhotos) {
            return false;
        }

        $listing = resolve(SeventRepositoryInterface::class)->find($id);

        if ($isUpdated) {
            policy_authorize(SeventPolicy::class, 'update', $context, $listing);
        }

        if (0 === count($attachedPhotos)) {
            $this->getModel()->newModelQuery()
                ->where([
                    'sevent_id' => $listing->entityId(),
                ])
                ->get()
                ->each(function ($currentPhoto) {
                    $currentPhoto->delete();
                });

            return true;
        }

        $newPhotos = array_filter($attachedPhotos, function ($attachedPhoto) {
            return $attachedPhoto['status'] == MetaFoxConstant::FILE_CREATE_STATUS;
        });

        $removedPhotos = array_filter($attachedPhotos, function ($attachedPhoto) {
            return $attachedPhoto['status'] == MetaFoxConstant::FILE_REMOVE_STATUS;
        });

        /*
         * Must remove first to update ordering before creating
         */
        $this->removePhotos($id, $removedPhotos);

        $this->createPhotos($id, $newPhotos);

        return true;
    }

    protected function createPhotos(int $listingId, array $newPhotos): void
    {
        if (!count($newPhotos)) {
            return;
        }

        $newOrdering = $this->getNextOrdering($listingId);

        foreach ($newPhotos as $newPhoto) {
            $tempFileId = Arr::get($newPhoto, 'temp_file');

            if (!$tempFileId) {
                continue;
            }

            $tempFile = upload()->getFile($tempFileId);

            $model = new Image();

            $model->fill([
                'sevent_id'    => $listingId,
                'image_file_id' => $tempFile->entityId(),
                'ordering'      => $newOrdering,
            ]);

            $success = $model->save();

            if ($success) {
                $newOrdering++;
            }

            upload()->rollUp($tempFileId);
        }
    }

    protected function getNextOrdering(int $listingId): int
    {
        $lastPhoto = $this->getModel()->newModelQuery()
            ->where([
                'sevent_id' => $listingId,
            ])
            ->orderByDesc('ordering')
            ->first();

        if (null === $lastPhoto) {
            return 1;
        }

        return (int) $lastPhoto->ordering + 1;
    }

    protected function removePhotos(int $listingId, array $removedPhotos): void
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

        $this->resetOrdering($listingId);
    }

    protected function resetOrdering(int $listingId): void
    {
        $photos = $this->getModel()->newModelQuery()
            ->where([
                'sevent_id' => $listingId,
            ])
            ->orderBy('ordering')
            ->get();

        if (0 === $photos->count()) {
            return;
        }

        $ordering = 1;

        foreach ($photos as $photo) {
            $photo->ordering = $ordering++;

            $photo->save();
        }
    }
}
