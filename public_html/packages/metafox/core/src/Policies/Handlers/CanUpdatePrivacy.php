<?php

namespace MetaFox\Core\Policies\Handlers;

use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanUpdatePrivacy implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        $isSponsoredOrIsFeatured = $resource->isSponsored() || $resource->isSponsoredInFeed();

        if ($resource instanceof HasFeature && $resource->is_featured) {
            $isSponsoredOrIsFeatured = true;
        }

        if ($newValue == MetaFoxPrivacy::ONLY_ME && $isSponsoredOrIsFeatured) {
            return false;
        }

        return true;
    }
}
