<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Jobs\SendFollowerNotification;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\ActivityRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class ModelCreatedListener.
 *
 * @ignore
 */
class ModelCreatedListener
{
    public function __construct(
        protected PageMemberRepositoryInterface $memberRepository,
        protected UserRepositoryInterface       $userRepository,
        protected ActivityRepositoryInterface   $activityRepository,
    ) {}

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

        if ($owner instanceof Page) {
            $this->handleSendNotificationToFollowers($model, $owner);
        }
    }

    /**
     * @param Content $item
     * @param Page    $resource
     *
     * @return void
     */
    protected function handleSendNotificationToFollowers(Content $item, Page $resource): void
    {
        $userItem = $item->user;
        if (!policy_check(PagePolicy::class, 'notifyFollowers', $userItem, $item)) {
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
