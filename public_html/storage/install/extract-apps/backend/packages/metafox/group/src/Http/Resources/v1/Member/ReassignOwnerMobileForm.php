<?php

namespace MetaFox\Group\Http\Resources\v1\Member;

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
 * Class ReassignOwnerForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class ReassignOwnerMobileForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('group::web.select_an_admin_to_reassign'))
            ->action('/group-member/reassign-owner')
            ->asPut()
            ->setValue([
                'group_id' => $this->resource->entityId(),
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::friendPicker('users')
                    ->setComponent('InviteFriendPicker')
                    ->endpoint('group-member')
                    ->params([
                        'group_id'         => $this->resource->entityId(),
                        'view'             => 'admin',
                        'excluded_user_id' => ':owner_id',
                    ])
                    ->required()
                    ->multiple(false)
                    ->placeholder(__p('friend::phrase.search_for_a_friend')),
                Builder::hidden('group_id'),
            );
    }
}
