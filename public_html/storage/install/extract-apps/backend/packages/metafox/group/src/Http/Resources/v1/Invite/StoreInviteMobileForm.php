<?php

namespace MetaFox\Group\Http\Resources\v1\Invite;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreInviteForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 *
 * @driverType form-mobile
 * @driverName group.invite.store
 */
class StoreInviteMobileForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id ?? 0);
    }

    protected function prepare(): void
    {
        $this->title(__p('group::phrase.invite_friend_to'))
            ->action('/group-invite')
            ->asPost()
            ->setValue([
                'group_id' => $this->resource->entityId(),
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::friendPicker('user_ids')
                    ->setComponent('InviteFriendPicker')
                    ->endpoint('friend/invite-to-owner')
                    ->params([
                        'owner_id'     => $this->resource->entityId(),
                        'privacy_type' => 'group_members',
                    ])
                    ->required()
                    ->multiple(true)
                    ->placeholder(__p('friend::phrase.search_for_a_friend')),
                Builder::hidden('group_id'),
            );
    }
}
