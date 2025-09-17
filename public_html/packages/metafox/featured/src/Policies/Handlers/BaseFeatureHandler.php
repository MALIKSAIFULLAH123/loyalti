<?php

namespace MetaFox\Featured\Policies\Handlers;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;

class BaseFeatureHandler
{
    protected const FEATURE_POLICY_METHOD = 'featureItem';

    protected function validateResourceStatus(Content $resource): bool
    {
        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->isDraft()) {
            return false;
        }

        return true;
    }

    protected function validatePermissionOnResource(User $user, Content $resource): bool
    {
        $policy = PolicyGate::getPolicyFor(get_class($resource));

        if (!is_object($policy)) {
            return true;
        }

        $featureMethod = self::FEATURE_POLICY_METHOD;

        if (!method_exists($policy, $featureMethod)) {
            return true;
        }

        return $policy->$featureMethod($user, $resource);
    }
}
