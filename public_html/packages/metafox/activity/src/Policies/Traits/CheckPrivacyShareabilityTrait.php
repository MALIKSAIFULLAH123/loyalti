<?php

namespace MetaFox\Activity\Policies\Traits;

use MetaFox\Platform\MetaFoxPrivacy;

trait CheckPrivacyShareabilityTrait
{
    /**
     * @param int|null $privacy
     *
     * @return bool
     */
    public function isPrivacyShareable(?int $privacy): bool
    {
        return in_array($privacy, [
            MetaFoxPrivacy::EVERYONE,
            MetaFoxPrivacy::MEMBERS,
            MetaFoxPrivacy::FRIENDS,
            MetaFoxPrivacy::FRIENDS_OF_FRIENDS,
        ]);
    }
}
