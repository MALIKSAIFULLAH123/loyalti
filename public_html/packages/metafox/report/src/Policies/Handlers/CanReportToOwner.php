<?php

namespace MetaFox\Report\Policies\Handlers;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasReportToOwner;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanReportToOwner implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue=null): bool
    {
        if (!$resource instanceof Entity) {
            return false;
        }

        $canReport = (new CanReportItem())->check($entityType, $user, $resource);
        if ($canReport == false) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource->owner instanceof HasReportToOwner) {
            return false;
        }

        return true;
    }
}
