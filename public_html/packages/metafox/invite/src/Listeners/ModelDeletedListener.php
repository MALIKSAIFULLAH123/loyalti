<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class ModelDeletedListener.
 * @ignore
 */
class ModelDeletedListener
{
    public function __construct(
        protected InviteRepositoryInterface     $repository,
        protected InviteCodeRepositoryInterface $codeRepository,
    ) {}

    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        if ($model instanceof Invite) {
            app('events')->dispatch('activitypoint.decrease_user_point', [$model->user, $model, Invite::ACTION_CREATE]);

            return;
        }

        if ($model instanceof User) {
            $this->deleteInvites($model);
            $this->deleteInviteCodes($model);
        }
    }

    protected function deleteInvites(User $model): void
    {
        $this->repository->deleteUserData($model);
    }

    protected function deleteInviteCodes(User $model): void
    {
        $this->codeRepository->deleteUserData($model);
    }
}
