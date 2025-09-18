<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use MetaFox\Group\Jobs\SendFollowerNotification;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\ActivityRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use Throwable;

/**
 * Class ModelCreatedListener.
 *
 * @ignore
 */
class ModelCreatedListener
{
    public function __construct(
        protected MemberRepositoryInterface   $memberRepository,
        protected UserRepositoryInterface     $userRepository,
        protected ActivityRepositoryInterface $activityRepository,
    ) {
    }

    /**
     * @param Model $model
     *
     * @return void
     */
    public function handle(Model $model): void
    {
        $this->handleWriteActivity($model);
        if (!$model instanceof Content) {
            return;
        }

        $owner = $model->owner;

        if ($owner instanceof Group) {
            try {
                $ignore = app('events')->dispatch('group.approved_model_notification.ignore_sending', [$model, $owner], true);
            } catch (Throwable $exception) {
                $ignore = null;

                Log::error('error with ignore approved model notification in group message: ' . $exception->getMessage());
                Log::error('error with ignore approved model notification in group trace: ' . $exception->getTraceAsString());
            }

            if (true !== $ignore) {
                $this->handleSendNotificationToFollowers($model, $owner);
            }
        }
    }

    /**
     * @param Content $item
     * @param Group   $resource
     *
     * @return void
     */
    protected function handleSendNotificationToFollowers(Content $item, Group $resource): void
    {
        if (!policy_check(GroupPolicy::class, 'notifyFollowers', $item->user, $item)) {
            return;
        }

        SendFollowerNotification::dispatch($item, $resource);
    }

    protected function handleWriteActivity(?Model $item): void
    {
        if (!$item instanceof Entity) {
            return;
        }

        $user = $item->user;

        if (!$user instanceof User) {
            return;
        }

        $this->activityRepository->createActivity($user, $item);
    }
}
