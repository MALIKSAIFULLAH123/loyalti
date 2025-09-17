<?php

namespace MetaFox\Advertise\Listeners;

use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class UnsponsorFeedListener
{
    public function handle(User $user, Content $resource): void
    {
        resolve(SponsorRepositoryInterface::class)->unsponsorFeed($user, $resource);
    }
}
