<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice;

use Illuminate\Support\Arr;
use MetaFox\Form\Builder;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class RenewSubscriptionInvoiceForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class RenewSubscriptionInvoiceForm extends GatewayForm
{
    protected function prepare(): void
    {
        $this->title(__p('subscription::phrase.renew_subscription'))
            ->action('/subscription-invoice/renew/' . $this->resource->entityId())
            ->asPatch()
            ->secondAction('@redirectTo');
    }

    protected function setFooterFields(): void
    {
        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('subscription::phrase.renew')),
                Builder::cancelButton(),
            );
    }

    protected function getGatewayParams(): array
    {
        return array_merge(parent::getGatewayParams(), [
            'entity' => $this->resource?->entityType(),
            'price'  => $this->resource?->recurring_price,
        ]);
    }
}
