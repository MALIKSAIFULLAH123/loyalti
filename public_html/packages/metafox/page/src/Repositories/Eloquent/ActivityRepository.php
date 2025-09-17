<?php

namespace MetaFox\Page\Repositories\Eloquent;

use MetaFox\Page\Jobs\DeleteActivitiesJob;
use MetaFox\Page\Models\Activity;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\ActivityRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class ActivitiesRepository
 *
 */
class ActivityRepository extends AbstractRepository implements ActivityRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return Activity::class;
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
        $page = $this->getOwnerItem($item);
        if (!$page instanceof Page) {
            return;
        }

        $attributes = [
            'page_id'   => $page->entityId(),
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
     *
     * @return void
     */
    public function deleteActivity(User $context, Entity $item): void
    {
        $page = $this->getOwnerItem($item);
        if (!$page instanceof Page) {
            return;
        }

        $attributes = [
            'page_id'   => $page->entityId(),
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

        if (!$ownerItem instanceof Page) {
            $ownerItem = $ownerItem?->owner;
        }

        return $ownerItem;
    }

    public function deleteActivities(Page $page, User $user): void
    {
        DeleteActivitiesJob::dispatch([
            'page_id'   => $page->id,
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
        ]);
    }
}
