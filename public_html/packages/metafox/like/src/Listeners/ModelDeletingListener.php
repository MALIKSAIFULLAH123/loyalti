<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Like\Listeners;

use MetaFox\Like\Jobs\DeleteLikeByItemJob;
use MetaFox\Like\Repositories\LikeRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Class ModelDeletingListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ModelDeletingListener
{
    /**
     * @param User|Content $model
     */
    public function handle($model): void
    {
        $likeRepository = resolve(LikeRepositoryInterface::class);

        if ($model instanceof User) {
            $likeRepository->deleteByUser($model);
        }

        if ($model instanceof Content) {
            DeleteLikeByItemJob::dispatch($model->entityId(), $model->entityType());
        }
    }
}
