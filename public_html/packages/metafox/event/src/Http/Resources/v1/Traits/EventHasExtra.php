<?php

namespace MetaFox\Event\Http\Resources\v1\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Support\ResourcePermission;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

/**
 * @property Content $resource
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait EventHasExtra
{
    use HasExtra;

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getEventExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Event::class);
        if (!$policy instanceof EventPolicy) {
            abort(400, 'Missing Policy');
        }

        $extra   = $this->getExtra();
        $context = user();
        $event   = $this->resource;

        return array_merge($extra, [
            ResourcePermission::CAN_VIEW_DISCUSSION     => $policy->viewDiscussion($context, $event),
            ResourcePermission::CAN_VIEW_HOSTS          => $policy->viewHosts($context, $event),
            ResourcePermission::CAN_VIEW_MEMBERS        => $policy->viewMembers($context, $event),
            ResourcePermission::CAN_CREATE_DISCUSSION   => $policy->createDiscussion($context, $event),
            ResourcePermission::CAN_MANAGE_PENDING_POST => $policy->managePendingPosts($context, $event),
            ResourcePermission::CAN_INVITE              => $policy->invite($context, $event),
            ResourcePermission::CAN_MANAGE_HOST         => $policy->manageHosts($context, $event),
            ResourcePermission::CAN_RSVP                => $policy->updateRsvp($context, $event),
            ResourcePermission::CAN_MASS_EMAIL          => $policy->massEmail($context, $event),
            ResourcePermission::CAN_DUPLICATE_EVENT     => $policy->duplicateEvent($context, $event),
            ResourcePermission::CAN_EXPORT              => $policy->export($context, $event),
            ResourcePermission::CAN_MASS_INVITE         => $policy->massInvite($context, $event),
        ]);
    }
}
