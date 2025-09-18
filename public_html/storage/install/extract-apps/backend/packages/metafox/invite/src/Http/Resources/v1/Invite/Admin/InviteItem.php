<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Invite\Models\Invite as Model;
use MetaFox\Invite\Support\Facades\Invite as InviteFacade;
use MetaFox\Platform\Contracts\User;

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
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'address'       => $this->resource->email ?? $this->resource->phone_number,
            'is_pending'    => $this->resource->isPending(),
            'expired_at'    => $this->resource->expired_at,
            'status'        => InviteFacade::getStatusPhrase($this->resource->status_id),
            'created_at'    => $this->resource->created_at,
            'updated_at'    => $this->resource->updated_at,
            'user'          => $this->getResourceByUser($this->resource->user),
            'owner'         => $this->getResourceByUser($this->resource->owner),
        ];
    }

    protected function getResourceByUser($user): ?JsonResource
    {
        if (!$user instanceof User) {
            return null;
        }

        $isDeleted = $user->isDeleted();

        return new JsonResource([
            'display_name'  => $this->when($isDeleted, __p('core::phrase.deleted_user'), $user->display_name),
            'created_at' => $user->created_at,
            'url'        => $this->when($isDeleted, null, $user->toUrl()),
        ]);
    }
}
