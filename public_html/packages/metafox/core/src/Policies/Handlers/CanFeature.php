<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Policies\Handlers;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanFeature implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$resource instanceof HasFeature || !$resource instanceof Content) {
            return false;
        }

        if (!$user->hasPermissionTo("$entityType.feature")) {
            return false;
        }

        if ($resource instanceof Content) {
            if (!$resource->isApproved()) {
                return false;
            }
        }

        // todo risky convert object int when check null == $newValue
        if (is_int($newValue) && $newValue == $resource->is_featured) {
            return false;
        }

        return $this->validatePermissionOnResource($user, $resource);
    }

    protected function validatePermissionOnResource(User $user, Content $resource): bool
    {
        $policy = PolicyGate::getPolicyFor(get_class($resource));

        if (!is_object($policy)) {
            return true;
        }

        $featureMethod = 'featureItem';

        if (!method_exists($policy, $featureMethod)) {
            return true;
        }

        return $policy->$featureMethod($user, $resource);
    }
}
