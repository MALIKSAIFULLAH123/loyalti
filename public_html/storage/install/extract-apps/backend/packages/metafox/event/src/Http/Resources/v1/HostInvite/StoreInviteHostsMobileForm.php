<?php

namespace MetaFox\Event\Http\Resources\v1\HostInvite;

use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreInviteHostsMobileForm.
 *
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverType form-mobile
 * @driverName event.invite_hosts.store
 */
class StoreInviteHostsMobileForm extends AbstractForm
{
    public function boot(EventRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id ?? 0);
    }

    protected function prepare(): void
    {
        $this->title(__p('event::web.invite_hosts'))
            ->action('/event-host-invite')
            ->asPost()
            ->setValue([
                'event_id' => $this->resource->entityId(),
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::friendPicker('users')
                    ->required()
                    ->multiple(true)
                    ->setComponent('InviteFriendPicker')
                    ->endpoint('friend/invite-to-item')
                    ->placeholder(__p('friend::phrase.search_for_friends'))
                    ->params([
                        'owner_id'  => $this->resource->ownerId(),
                        'q'         => '',
                        'item_id'   => $this->resource->entityId(),
                        'item_type' => $this->resource->entityType(),
                    ]),
                Builder::hidden('event_id'),
            );
    }
}
