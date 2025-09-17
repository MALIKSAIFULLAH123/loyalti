<?php

namespace MetaFox\Friend\Http\Resources\v1\FriendList;

use MetaFox\Friend\Models\FriendList as Model;
use MetaFox\Friend\Policies\FriendListPolicy;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class EditFriendListForm.
 * @property Model $resource
 */
class EditFriendListMobileForm extends CreateFriendListMobileForm
{
    public function boot(FriendListRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();

        $this->resource = $repository->find($id);

        policy_authorize(FriendListPolicy::class, 'update', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $this->asPut()
            ->title(__p('core::phrase.edit_friend_list'))
            ->setValue([
                'name'  => $this->resource->name,
                'users' => ResourceGate::items($this->resource->users),
            ])
            ->action('friend/list/' . $this->resource->entityId());
    }
}
