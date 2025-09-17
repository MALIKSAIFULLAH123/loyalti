<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Jobs\SendFollowerNotification;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class ModelApprovedListener.
 *
 * @ignore
 */
class ModelApprovedListener
{
    public function __construct(
        protected MemberRepositoryInterface $memberRepository,
        protected UserRepositoryInterface   $userRepository,
        protected GroupRepositoryInterface  $groupRepository
    ) {
    }

    /**
     * @param User|null $context
     * @param Model     $model
     *
     * @return void
     */
    public function handle(?User $context, Model $model): void
    {
        if ($model instanceof Group) {
            $this->groupRepository->handleSendInviteNotification($model);

            return;
        }

        if (!$model instanceof Content) {
            return;
        }

        $owner = $model->owner;

        if ($owner instanceof Group) {
            $this->handleSendNotificationToFollowers($context, $model, $owner);
        }
    }

    /**
     * @param User|null $context
     * @param Content   $item
     * @param Group     $resource
     *
     * @return void
     */
    protected function handleSendNotificationToFollowers(?User $context, Content $item, Group $resource): void
    {
        $userItem = $item->user;
        if (!policy_check(GroupPolicy::class, 'notifyFollowers', $userItem, $item)) {
            return;
        }

        SendFollowerNotification::dispatch($item, $resource);
    }
}
