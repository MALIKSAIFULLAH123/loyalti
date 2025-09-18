<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\Marketplace\Models\Image;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Marketplace\Repositories\ImageRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class ImageRepository.
 *
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
     * @param User       $context
     * @param int        $id
     * @param array|null $attachedPhotos
     * @param bool       $isCreated
     *
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateImages(User $context, int $id, ?array $attachedPhotos, bool $isUpdated = true): bool
    {
        if (null === $attachedPhotos) {
            return false;
        }

        $listing = resolve(ListingRepositoryInterface::class)->find($id);

        if ($isUpdated) {
            policy_authorize(ListingPolicy::class, 'update', $context, $listing);
        }

        if (0 === count($attachedPhotos)) {
            $this->getModel()->newModelQuery()
                ->where([
                    'listing_id' => $listing->entityId(),
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
        $this->removePhotos($id, $removedPhotos);
        $this->createPhotos($id, $newPhotos);
        $this->updatePhotos($id, $updatedPhotos);

        return true;
    }

    protected function createPhotos(int $listingId, array $newPhotos): void
    {
        if (!count($newPhotos)) {
            return;
        }

        foreach ($newPhotos as $newPhoto) {
            $tempFileId  = Arr::get($newPhoto, 'temp_file');
            $newOrdering = Arr::get($newPhoto, 'ordering');

            if (!$tempFileId) {
                continue;
            }

            $tempFile = upload()->getFile($tempFileId);

            $model = new Image();

            $model->fill([
                'listing_id'    => $listingId,
                'image_file_id' => $tempFile->entityId(),
                'ordering'      => $newOrdering,
            ]);

            $model->save();

            upload()->rollUp($tempFileId);
        }
    }

    protected function updatePhotos(int $listingId, array $updatePhotos): void
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

    protected function getNextOrdering(int $listingId): int
    {
        $lastPhoto = $this->getModel()->newModelQuery()
            ->where([
                'listing_id' => $listingId,
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

    }

    protected function resetOrdering(int $listingId): void
    {
        $photos = $this->getModel()->newModelQuery()
            ->where([
                'listing_id' => $listingId,
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
