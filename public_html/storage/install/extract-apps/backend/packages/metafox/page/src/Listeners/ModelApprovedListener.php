<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Jobs\SendFollowerNotification;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Models\User as UserModel;

/**
 * Class ModelApprovedListener.
 *
 * @ignore
 */
class ModelApprovedListener
{
    public function __construct(
        protected PageMemberRepositoryInterface $memberRepository,
        protected UserRepositoryInterface $userRepository,
        protected PageRepositoryInterface $pageRepository
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
        if ($model instanceof UserModel) {
            $this->memberRepository->followPagesOnSignup($model);
        }

        if ($model instanceof Page) {
            $this->pageRepository->handleSendInviteNotification($model);

            return;
        }

        if (!$model instanceof Content) {
            return;
        }

        $owner = $model->owner;

        if ($owner instanceof Page) {
            $this->handleSendNotificationToFollowers($context, $model, $owner);
        }
    }

    /**
     * @param User|null $context
     * @param Content   $item
     * @param Page      $resource
     *
     * @return void
     */
    protected function handleSendNotificationToFollowers(?User $context, Content $item, Page $resource): void
    {
        $userItem = $item->user;
        if (!policy_check(PagePolicy::class, 'notifyFollowers', $userItem, $item)) {
            return;
        }

        SendFollowerNotification::dispatch($item, $resource);
    }
}
