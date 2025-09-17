<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Listeners;

use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\User;
use MetaFox\Saved\Repositories\SavedAggRepositoryInterface;
use MetaFox\Saved\Repositories\SavedListMemberRepositoryInterface;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;

/**
 * Class ModelDeletedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ModelDeletedListener
{
    public function __construct(
        protected SavedListRepositoryInterface $savedListRepository,
        protected SavedRepositoryInterface $savedRepository,
        protected SavedAggRepositoryInterface $savedAggRepository,
        protected SavedListMemberRepositoryInterface $savedListMemberRepository,
    ) {
    }

    /**
     * @param mixed $model
     *
     * @return void
     */
    public function handle($model): void
    {
        if ($model instanceof User) {
            $this->savedListRepository->deleteForUser($model);
            $this->savedRepository->deleteForUser($model);
            $this->savedAggRepository->deleteForUser($model);
            $this->savedListMemberRepository->deleteUserData($model);
        }

        if ($model instanceof HasSavedItem) {
            $this->savedRepository->deleteForItem($model);
        }
    }
}
