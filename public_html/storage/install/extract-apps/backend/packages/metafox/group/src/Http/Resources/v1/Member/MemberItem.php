<?php

namespace MetaFox\Group\Http\Resources\v1\Member;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Member as Model;
use MetaFox\Group\Support\Browse\Traits\Member\ExtraTrait;
use MetaFox\Group\Support\Membership;
use MetaFox\User\Http\Resources\v1\User\UserItem;

/**
 * Class GroupMemberItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class MemberItem extends JsonResource
{
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $isMuted = Membership::isMuted($this->resource->group_id, $this->resource->userId());
        $user    = $this->resource->user;
        $group   = $this->resource->group;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'group',
            'resource_name' => $this->resource->entityType(),
            'user'          => new UserItem($user),
            'group_id'      => $this->resource->group_id,
            'is_muted'      => $isMuted,
            'member_type'   => $this->resource->member_type,
            'extra'         => $this->getExtra(),
            'role_label'    => $group->getRoleLabel($user),
        ];
    }
}
