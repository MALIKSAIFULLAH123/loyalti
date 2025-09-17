<?php

namespace MetaFox\Advertise\Listeners;

use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class CanPurchaseSponsorListener
{
    public function handle(User $context, Content $content): bool
    {
        return policy_check(SponsorPolicy::class, 'purchaseSponsor', $context, $content);
    }
}
