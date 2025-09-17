<?php

namespace MetaFox\Page\Http\Resources\v1\PageInvite;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;

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
 * @driverType form
 * @driverName page.invite.store
 */
class StoreInviteMobileForm extends AbstractForm
{
    public function boot(PageRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id ?? 0);
    }

    protected function prepare(): void
    {
        $this->title(__p('page::phrase.invite_friend_to'))
            ->action('/page-invite')
            ->asPost()
            ->setValue([
                'page_id' => $this->resource->entityId(),
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
                        'privacy_type' => 'page_members',
                    ])
                    ->required()
                    ->multiple(true)
                    ->placeholder(__p('friend::phrase.search_for_a_friend')),
                Builder::hidden('page_id'),
            );
    }
}
