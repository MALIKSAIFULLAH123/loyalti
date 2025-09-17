<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateSubscriptionCancelReasonForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateSubscriptionCancelReasonForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('subscription::admin.create_reason'))
            ->action(apiUrl('admin.subscription.cancel-reason.store'))
            ->asPost();
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::translatableText('title')
                    ->required()
                    ->label(__p('core::phrase.title'))
                    ->description(__p('subscription::admin.maximum_number_characters', ['number' => MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH]))
                    ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                    ->yup(
                        Yup::string()
                            ->required()
                            ->nullable()
                    )
                    ->buildFields()
            );

        $this->addDefaultFooter();
    }
}
