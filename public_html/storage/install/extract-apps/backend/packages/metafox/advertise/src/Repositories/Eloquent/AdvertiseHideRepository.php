<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Policies\AdvertiseHidePolicy;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Advertise\Repositories\AdvertiseHideRepositoryInterface;
use MetaFox\Advertise\Models\AdvertiseHide;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class AdvertiseHideRepository.
 */
class AdvertiseHideRepository extends AbstractRepository implements AdvertiseHideRepositoryInterface
{
    public function model()
    {
        return AdvertiseHide::class;
    }

    public function createHide(User $context, Entity $item): AdvertiseHide
    {
        policy_authorize(AdvertiseHidePolicy::class, 'hide', $context, $item);

        return AdvertiseHide::firstOrCreate([
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]);
    }

    public function deleteHide(User $context, Entity $item): bool
    {
        policy_authorize(AdvertiseHidePolicy::class, 'unhide', $context, $item);

        $model = AdvertiseHide::query()
            ->where([
                'item_id'   => $item->entityId(),
                'item_type' => $item->entityType(),
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
            ])
            ->first();

        if (null === $model) {
            return false;
        }

        $model->delete();

        return true;
    }

    public function deleteHidesByItem(Entity $item): void
    {
        AdvertiseHide::query()
            ->where([
                'item_id'   => $item->entityId(),
                'item_type' => $item->entityType(),
            ])->delete();
    }

    public function isHidden(User $context, Entity $item): bool
    {
        $hide = AdvertiseHide::query()
            ->where([
                'item_id'   => $item->entityId(),
                'item_type' => $item->entityType(),
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
            ])
            ->first();

        if (null === $hide) {
            return false;
        }

        return true;
    }

    public function getHiddenItemIds(User $context, string $itemType): array
    {
        return AdvertiseHide::query()
            ->where([
                'item_type' => $itemType,
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
            ])
            ->pluck('item_id')
            ->toArray();
    }
}
