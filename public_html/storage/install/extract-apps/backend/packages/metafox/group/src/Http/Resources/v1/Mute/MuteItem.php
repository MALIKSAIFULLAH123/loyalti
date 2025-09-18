<?php

namespace MetaFox\Group\Http\Resources\v1\Mute;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Models\Mute as Model;
use MetaFox\Group\Policies\MemberPolicy;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\Membership;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\User\Http\Resources\v1\User\UserItem;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class MuteItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class MuteItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $isMuted = Membership::isMuted($this->resource->entityId(), $this->resource->userId());

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'group',
            'resource_name' => $this->resource->entityType(),
            'user'          => new UserItem($this->resource->user),
            'group_id'      => $this->resource->group_id,
            'is_muted'      => $isMuted,
            'expired_at'    => $this->resource?->expired_at,
            'extra'         => $this->getExtra(),
        ];
    }

    protected function memberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    protected function getExtra(): array
    {
        $context = user();

        /** @var MemberPolicy $policy */
        $policy      = PolicyGate::getPolicyFor(Member::class);
        $groupMember = $this->memberRepository()->getGroupMember($this->resource->group_id, $this->resource->userId());

        return [
            'can_unmute_in_group' => $groupMember instanceof Member && $policy->unmuteInGroup($context, $groupMember),
        ];
    }
}
