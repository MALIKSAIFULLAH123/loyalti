<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedListMember;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Models\SavedListMember as Model;
use MetaFox\Saved\Policies\SavedListPolicy;
use MetaFox\User\Http\Resources\v1\User\UserItem;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class SavedListItem.
 * @property Model $resource
 */
class MemberItem extends JsonResource
{
    public function toArray($request)
    {
        $user       = resolve(UserRepositoryInterface::class)->find($this->resource->user_id);

        return [
            'id'              => $this->resource->user_id,
            'full_name'       => $user->full_name,
            'avatar'          => $user->profile->avatars,
            'resource_name'   => 'member',
            'module_name'     => 'saved',
            'profile_page_id' => 0,
            'user_name'       => $user->user_name,
            'email'           => $user->email,
            'short_name'      => UserFacade::getShortName($user->full_name),
            'link'            => $user->toLink(),
            'url'             => $user->toUrl(),
            'is_deleted'      => $user->isDeleted(),
            'is_owner'        => $this->resource->collection?->isUser($user),
            'collection_id'   => $this->resource->list_id,
            'user'            => new UserItem($user),
            'extra'           => $this->getExtra(),
        ];
    }

    protected function getExtra(): array
    {
        $context    = user();

        /** @var SavedListPolicy $policy */
        $policy                        = PolicyGate::getPolicyFor(SavedList::class);
        $canRemoveMemberFromCollection = $policy->removeMember(
            $context,
            $this->resource->collection,
            $this->resource->user_id
        );

        return [
            'can_remove' => $canRemoveMemberFromCollection,
        ];
    }
}
