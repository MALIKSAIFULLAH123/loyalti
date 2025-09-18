<?php

namespace MetaFox\Group\Repositories\Eloquent;

use MetaFox\Group\Jobs\DeleteActivitiesJob;
use MetaFox\Group\Models\Activity;
use MetaFox\Group\Models\Block;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\ActivityRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasItemMorph;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class BlockRepository.
 * @method Block getModel()
 */
class ActivityRepository extends AbstractRepository implements ActivityRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return Activity::class;
    }

    /**
     * @return GroupRepositoryInterface
     */
    private function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    /**
     * @return MemberRepositoryInterface
     */
    private function memberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     *
     * @param User   $context
     * @param Entity $item
     *
     * @return void
     */
    public function createActivity(User $context, Entity $item): void
    {
        $group = $this->getOwnerItem($item);
        if (!$group instanceof Group) {
            return;
        }

        $attributes = [
            'group_id'  => $group->entityId(),
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ];

        $model = $this->getModel()->newInstance();
        $model->fill($attributes)->save();
    }

    /**
     * @param User   $context
     * @param Entity $item
     * @param array  $attributes
     *
     * @return void
     */
    public function deleteActivity(User $context, Entity $item): void
    {
        $group = $this->getOwnerItem($item);
        if (!$group instanceof Group) {
            return;
        }

        $attributes = [
            'group_id'  => $group->entityId(),
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ];

        $this->getModel()->newQuery()->where($attributes)->delete();
    }

    protected function getOwnerItem(Entity $item, int $offset = 0): ?User
    {
        $ownerItem = $item->owner;
        if ($item?->item_id != null && $item?->item_type != null) {
            $item = $item?->item;
        }

        if ($item instanceof Entity && $offset < 5) {
            $offset++;
            $ownerItem = $this->getOwnerItem($item, $offset);
        }

        if (!$ownerItem instanceof Group) {
            $ownerItem = $ownerItem?->owner;
        }

        return $ownerItem;
    }

    public function deleteActivities(Group $group, User $user): void
    {
        DeleteActivitiesJob::dispatch([
            'group_id'  => $group->id,
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
        ]);
    }
}
