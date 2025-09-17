<?php

namespace MetaFox\Friend\Http\Resources\v1\FriendList;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
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
 * Class AddItemsForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName friend.friend_list.add_items
 * @driverType form
 */
class AddItemsForm extends AbstractForm
{
    public function boot(FriendListRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        // Init resource
        $this->resource = $repository->find($id);

        // Check policy
        policy_authorize(FriendListPolicy::class, 'update', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $this->asPost()->title(__p('friend::phrase.add_friend_to_list'))
            ->action(url_utility()->makeApiUrl("friend/list/add-friend/{$this->resource->entityId()}"))
            ->setValue([
                'users' => $this->resource->users()->get(),
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::friendPicker('users')
                    ->multiple(true)
                    ->placeholder(__p('friend::phrase.search_for_friends'))
            );

        $this->addDefaultFooter(true);
    }
}
