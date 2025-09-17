<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Invite\Models\Invite as Model;
use MetaFox\Invite\Policies\InvitePolicy;
use MetaFox\Invite\Support\Facades\Invite as InviteFacade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class InviteItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class InviteItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $inviter = $this->resource->user;
        $policy  = new InvitePolicy();
        
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'address'       => $this->resource->email ?? $this->resource->phone_number,
            'email'         => $this->resource->email,
            'is_pending'    => $this->resource->isPending(),
            'phone_number'  => $this->resource->phone_number,
            'expired_at'    => $this->resource->expired_at,
            'status'        => InviteFacade::getStatusPhrase($this->resource->status_id),
            'status_info'   => InviteFacade::getStatusInfo($this->resource->status_id),
            'created_at'    => $this->resource->created_at,
            'updated_at'    => $this->resource->updated_at,
            'user'          => ResourceGate::asDetail($this->resource->user),
            'owner'         => $this->getResourceOwner(),
            'extra'         => [
                'can_create' => $policy->create($inviter),
            ],
        ];
    }

    protected function getResourceOwner(): ?JsonResource
    {
        $owner = $this->resource->owner;
        if (!$owner instanceof User) {
            return null;
        }

        if ($owner->isDeleted()) {
            return null;
        }

        return ResourceGate::asDetail($owner, false);
    }
}
