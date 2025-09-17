<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

use MetaFox\Marketplace\Policies\InvoicePolicy;
use MetaFox\Marketplace\Repositories\InvoiceRepositoryInterface;
use MetaFox\Marketplace\Support\Facade\Listing;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;

class PaymentInvoiceForm extends GatewayForm
{
    public function boot(?int $id = null): void
    {
        $this->resource = resolve(InvoiceRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(InvoicePolicy::class, 'repayment', $context, $this->resource, true);

        $this->processMultiStepForm();
    }

    protected function processMultiStepForm(): void
    {
        if (!Listing::isUsingMultiStepFormForEwallet()) {
            return;
        }

        $actionMeta = $this->getActionMeta();

        $this->setMultiStepFormMeta($actionMeta->toArray());
    }

    protected function prepare(): void
    {
        $this->title(__p('payment::phrase.select_payment_gateway'))
            ->action('marketplace-invoice/repayment/' . $this->resource->entityId())
            ->secondAction('@redirectTo')
            ->asPut();
    }

    protected function getGatewayParams(): array
    {
        $listing = $this->resource->listing;

        if (null === $listing) {
            return [];
        }

        return array_merge(parent::getGatewayParams(), [
            'payee_id' => $listing->userId(),
            'price'    => $this->resource->price,
        ]);
    }
}
