<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;

class MarkAsApproveListener
{
    public function __construct(protected FeedRepositoryInterface $repository)
    {
    }

    public function handle(User $user, User $owner): void
    {
        if (!$owner instanceof HasPrivacyMember) {
            return;
        }

        $this->repository->approvePendingFeeds($user, $owner);
    }
}
