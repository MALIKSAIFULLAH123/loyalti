<?php

namespace MetaFox\Friend\Http\Resources\v1\FriendList;

use MetaFox\Friend\Models\FriendList as Model;
use MetaFox\Friend\Policies\FriendListPolicy;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditFriendListForm.
 * @property ?Model $resource
 */
class EditFriendListForm extends CreateFriendListForm
{
    public function boot(FriendListRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();

        $this->resource = $repository->find($id);
        $this->resource->loadMissing(['users']);

        policy_authorize(FriendListPolicy::class, 'update', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $this->asPut()
            ->title(__p('core::phrase.edit_friend_list'))
            ->action('friend/list/:id')
            ->setValue([
                'name'  => $this->resource->name,
                'users' => $this->resource->users,
            ]);
    }
}
