<?php

namespace MetaFox\Event\Http\Resources\v1\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Policies\MemberPolicy;
use MetaFox\Event\Support\ResourcePermission;
use MetaFox\Platform\Facades\PolicyGate;

/**
 * @property Member $resource
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait MemberHasExtra
{
    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getMemberExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Member::class);

        if (!$policy instanceof MemberPolicy) {
            abort(400, 'Missing Policy');
        }

        $context = user();

        return [
            ResourcePermission::CAN_DELETE      => $policy->deleteMember($context, $this->resource) && $this->resource->isRole(Member::ROLE_MEMBER),
            ResourcePermission::CAN_REMOVE_HOST => $policy->removeHost($context, $this->resource),
        ];
    }
}
