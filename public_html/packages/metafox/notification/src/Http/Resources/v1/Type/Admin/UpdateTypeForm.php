<?php

namespace MetaFox\Notification\Http\Resources\v1\Type\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Notification\Models\Type as Model;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateTypeForm.
 * @property Model $resource
 */
class UpdateTypeForm extends AbstractForm
{
    public function boot(?int $id = null): void
    {
        $this->resource = resolve(TypeRepositoryInterface::class)->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('notification::phrase.edit_notification_type'))
            ->action(apiUrl('admin.notification.type.update', ['type' => $this->resource->entityId()]))
            ->asPut()
            ->setValue(new TypeItem($this->resource));
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('type')
                ->required()
                ->returnKeyType('next')
                ->label(__p('core::phrase.type'))
                ->description(__p('core::phrase.read_only_property'))
                ->readOnly(),
            Builder::checkbox('is_active')
                ->label(__p('notification::phrase.enable_this_notification_type')),

            //@todo: Need to implement mapping between type and channels
//            Builder::checkbox('is_request')
//                ->label(__p('notification::phrase.is_request_notification_type')),
//            Builder::checkbox('can_edit')
//                ->label(__p('notification::phrase.notification_type_can_edit')),
//            Builder::checkbox('database')
//                ->label(__p('notification::phrase.notification_type_enable_database_channel')),
//            Builder::checkbox('mail')
//                ->label(__p('notification::phrase.notification_type_enable_mail_channel')),
//            Builder::checkbox('mobile_push')
//                ->label(__p('notification::phrase.notification_type_enable_mobile_push_channel')),
        );

        $this->addDefaultFooter(true);
    }
}
