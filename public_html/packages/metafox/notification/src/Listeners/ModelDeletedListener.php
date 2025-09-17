<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Notification\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Notification\Repositories\NotificationRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;

/**
 * Class ModelDeletedListener.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class ModelDeletedListener
{
    private NotificationRepositoryInterface $repository;

    public function __construct(NotificationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handle(Model $model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        $this->repository->deleteMassNotificationByItem($model->entityId(), $model->entityType());
    }
}
