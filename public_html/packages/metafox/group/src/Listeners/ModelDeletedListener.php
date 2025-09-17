<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Repositories\ActivityRepositoryInterface;
use MetaFox\Group\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * Class ModelDeletedListener.
 *
 * @ignore
 */
class ModelDeletedListener
{
    public function __construct(
        protected AnnouncementRepositoryInterface $announcementRepository,
        protected ActivityRepositoryInterface     $activityRepository,
    ) {
    }

    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        $this->handleDeleteActivity($model);
        if (!$model instanceof Content) {
            return;
        }

        if ($model->entityType() == 'feed') {
            $this->handleAnnouncement($model);
        }
    }

    /**
     * @param Content $model
     */
    public function handleAnnouncement(Content $model): void
    {
        $this->announcementRepository->deleteByItem($model->entityId(), $model->entityType());
    }

    protected function handleDeleteActivity(?Model $item): void
    {
        if (!$item instanceof Entity) {
            return;
        }

        $user = $item->user;

        if (!$user instanceof User) {
            return;
        }

        $this->activityRepository->deleteActivity($user, $item);
    }
}
