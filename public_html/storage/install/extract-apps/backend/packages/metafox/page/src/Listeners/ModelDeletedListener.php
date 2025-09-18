<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\ActivityRepositoryInterface;
use MetaFox\Page\Repositories\PageClaimRepositoryInterface;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * Class ModelDeletedListener.
 * @ignore
 */
class ModelDeletedListener
{
    public function __construct(
        protected PageMemberRepositoryInterface $memberRepository,
        protected PageRepositoryInterface       $pageRepository,
        protected PageInviteRepositoryInterface $inviteRepository,
        protected PageClaimRepositoryInterface  $claimRepository,
        protected ActivityRepositoryInterface   $activityRepository,
    ) {}

    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        $this->handleDeleteActivity($model);

        if ($model instanceof Page) {
            return;
        }

        if ($model instanceof User) {
            $this->deleteUserData($model);
        }
    }

    protected function deleteUserData(User $model): void
    {
        $this->deletePage($model);
        $this->deleteInvites($model);
        $this->deleteMembers($model);
        $this->deleteClaims($model);
        $this->deleteActivity($model);
    }

    protected function deletePage(User $model): void
    {
        $this->pageRepository->deleteUserData($model);
    }

    protected function deleteInvites(User $model): void
    {
        $this->inviteRepository->deleteOwnerData($model);

        $this->inviteRepository->deleteUserData($model);
    }

    protected function deleteMembers(User $model): void
    {
        $this->memberRepository->deleteUserData($model);
    }

    protected function deleteClaims(User $model): void
    {
        $this->claimRepository->deleteUserData($model);
    }

    protected function deleteActivity(User $user): void
    {
        $this->activityRepository->deleteUserData($user);
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
