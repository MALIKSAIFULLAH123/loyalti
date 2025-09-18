<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;
use MetaFox\Subscription\Repositories\SubscriptionCancelReasonRepositoryInterface;
use MetaFox\Subscription\Support\Helper;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class DeleteSubscriptionCancelReasonForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteSubscriptionCancelReasonForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('subscription::admin.delete_reason'))
            ->action(apiUrl('admin.subscription.cancel-reason.destroy', [
                'cancel_reason' => $this->resource->entityId(),
            ]))
            ->asDelete()
            ->setValue([
                'delete_option' => 1,
            ]);
    }

    protected function initialize(): void
    {
        $customReasons = $this->getCustomReasonOptions();

        if (count($customReasons)) {
            foreach ($customReasons as $key => $customReason) {
                if ($customReason['value'] == $this->resource->entityId()) {
                    unset($customReasons[$key]);
                }
            }
            $customReasons = array_values($customReasons);
        }

        $hasCustomReasons = count($customReasons) > 0;

        $basic = $this->addBasic();

        $basic->addField(
            Builder::radioGroup('delete_option')
                ->label(__p('subscription::admin.select_an_action_to_all_cancelled_subscriptions_of_this_reason'))
                ->options($this->getOptions($hasCustomReasons))
        );

        if ($hasCustomReasons) {
            $basic->addField(
                Builder::choice('custom_reason')
                    ->options($customReasons)
                    ->multiple(false)
                    ->showWhen([
                        'eq',
                        'delete_option',
                        Helper::DELETE_REASON_CUSTOM,
                    ])
                    ->yup(
                        Yup::number()
                            ->when(
                                Yup::when('delete_option')
                                    ->is('2')
                                    ->then(
                                        Yup::number()
                                            ->required()
                                            ->setError('required', __p('subscription::admin.delete_cancel_reason_description'))
                                            ->setError('typeError', __p('subscription::admin.delete_cancel_reason_description')),
                                    )
                            )
                    )
            );
        }

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('core::phrase.delete')),
                Builder::cancelButton(),
            );
    }

    protected function getOptions(bool $hasCustomReasons): array
    {
        $options = [
            [
                'label' => __p('subscription::admin.move_all_cancelled_subscriptions_to_default_reason'),
                'value' => Helper::DELETE_REASON_DEFAULT,
            ],
        ];

        if ($hasCustomReasons) {
            $options[] = [
                'label' => __p('subscription::admin.select_another_reason_for_all_cancelled_subscriptions_belonging_to_this'),
                'value' => Helper::DELETE_REASON_CUSTOM,
            ];
        }

        return $options;
    }

    protected function getCustomReasonOptions(): array
    {
        $context = user();

        return resolve(SubscriptionCancelReasonRepositoryInterface::class)->getCustomReasonOptions($context);
    }
}
