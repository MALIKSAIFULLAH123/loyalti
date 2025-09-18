<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

use MetaFox\Form\Builder;
use MetaFox\Marketplace\Policies\InvoicePolicy;
use MetaFox\Marketplace\Repositories\InvoiceRepositoryInterface;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;

class ConfirmCancelInvoiceForm extends GatewayForm
{
    public function boot(?int $id = null): void
    {
        $this->resource = resolve(InvoiceRepositoryInterface::class)->find($id);
        $context        = user();

        policy_authorize(InvoicePolicy::class, 'cancel', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $this->title(__p('marketplace::phrase.listing_expired'))
            ->action(sprintf('marketplace-invoice/%s/cancel?listing_id=%d', $this->resource->entityId(), $this->resource->listing_id))
            ->secondAction('@redirectTo')
            ->asPatch();
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::typography('alert_confirm')
                ->plainText(__p('marketplace::phrase.cancel_invoices_for_pending_listing')),
        );

        $this->addFooter()->addFields(
            Builder::submit()
                ->label(__p('core::phrase.submit')),
            Builder::cancelButton()
        );
    }
}
