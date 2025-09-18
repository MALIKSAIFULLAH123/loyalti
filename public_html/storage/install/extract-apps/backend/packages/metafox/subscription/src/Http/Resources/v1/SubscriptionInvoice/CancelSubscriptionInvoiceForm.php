<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionCancelReason;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CancelSubscriptionInvoiceForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CancelSubscriptionInvoiceForm extends AbstractForm
{
    public function boot(SubscriptionInvoiceRepositoryInterface $repository, int $id = 0): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('subscription::phrase.cancel_subscription'))
            ->action('/subscription-invoice/cancel/' . $this->resource->entityId())
            ->asPatch();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::description()
                ->label(__p('subscription::phrase.we_are_sorry_you_are_thinking_of_cancelling_your_subscription_can_you_tell_us_why')),
        );

        $reasons = $this->getReasonOptions();

        if (count($reasons)) {
            $basic->addField(
                Builder::radioGroup('reason_id')
                    ->options($this->getReasonOptions())
                    ->yup(
                        Yup::string()
                            ->required()
                            ->setError(
                                'required',
                                __p('subscription::validation.choose_one_reason_before_cancelling_the_subscription')
                            )
                            ->setError(
                                'typeError',
                                __p('subscription::validation.choose_one_reason_before_cancelling_the_subscription')
                            ),
                    )
            );
        }

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('subscription::phrase.cancel_subscription'))
                    ->color('error')
                    ->variant('outlined'),
                Builder::cancelButton()
                    ->label(__p('subscription::phrase.keep_subscription')),
            );
    }

    protected function getReasonOptions(): array
    {
        $context = user();

        return SubscriptionCancelReason::getActiveOptions($context);
    }
}
