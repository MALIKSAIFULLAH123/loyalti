<?php

namespace MetaFox\Event\Http\Resources\v1\Invite;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Invite as Model;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Support\ResourcePermission;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\User\Http\Resources\v1\User\UserItem;

/**
 * Class InviteItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class InviteItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request                 $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'event',
            'resource_name' => $this->resource->entityType(),
            'status_id'     => $this->resource->status_id,
            'event_id'      => $this->resource->event_id,
            'user'          => new UserItem($this->resource->user),
            'owner'         => new UserItem($this->resource->owner),
            'extra'         => $this->getExtra(),
        ];
    }

    /**
     * @throws AuthenticationException
     */
    protected function getExtra(): array
    {
        $policy  = PolicyGate::getPolicyFor(Event::class);
        $context = user();
        $invite  = $this->resource;

        if (!$policy instanceof EventPolicy) {
            abort(400, 'Missing Policy');
        }

        return [
            ResourcePermission::CAN_REMOVE_INVITE => $policy->removeInvite($context, $invite),
        ];
    }
}
