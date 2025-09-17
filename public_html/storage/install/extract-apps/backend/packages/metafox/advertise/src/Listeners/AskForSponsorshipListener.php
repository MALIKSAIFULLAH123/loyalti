<?php
namespace MetaFox\Advertise\Listeners;

use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

class AskForSponsorshipListener
{
    public function handle(User $user, Content $content): bool
    {
        if (!Settings::get('advertise.purchase_sponsorship_after_creating_an_item')) {
            return false;
        }

        if (!policy_check(SponsorPolicy::class, 'purchaseSponsor', $user, $content)) {
            return false;
        }

        return true;
    }
}
