<?php

namespace MetaFox\Advertise\Listeners;

use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class SponsorFeedFreeListener
{
    public function handle(User $user, Content $resource): bool
    {
        resolve(SponsorRepositoryInterface::class)->sponsorFeed($user, $resource);

        return true;
    }
}
