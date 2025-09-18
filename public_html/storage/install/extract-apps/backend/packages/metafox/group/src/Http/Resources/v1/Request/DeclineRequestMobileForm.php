<?php

namespace MetaFox\Group\Http\Resources\v1\Request;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Group\Models\Request as Model;
use MetaFox\Group\Policies\RequestPolicy;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class DeclineRequestMobileForm.
 * @property Model $resource
 * @driverType form
 * @driverName group.group_request.decline
 */
class DeclineRequestMobileForm extends AbstractForm
{
    public function boot(int $id, RequestRepositoryInterface $repository): void
    {
        $this->resource = $repository->find($id);

        policy_authorize(RequestPolicy::class, 'approve', user(), $this->resource);
    }

    protected function prepare(): void
    {
        $this->title(__p('group::phrase.decline_request'))
            ->action(sprintf('group-request/%s/decline', $this->resource->entityId()))
            ->asPatch()
            ->setValue([]);
    }

    public function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::textArea('reason')
                ->required()
                ->label(__p('group::phrase.please_give_the_reason_why_you_decline_this_request'))
                ->returnKeyType('default')
                ->yup(
                    Yup::string()->nullable()->required()
                ),
            Builder::checkbox('has_send_notification')
                ->label(__p('group::phrase.send_a_notification_to_the_declined_user'))
        );
    }
}
