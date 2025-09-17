<?php
namespace MetaFox\Featured\Http\Resources\v1\Invoice;

use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Policies\InvoicePolicy;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Form\Builder;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;
use MetaFox\Platform\MetaFox;

/**
 * @property Invoice $resource
 */
class PaymentInvoiceForm extends GatewayForm
{
    protected function prepare(): void
    {
        $this->title(__p('payment::phrase.select_payment_gateway'))
            ->action('featured/invoice/payment')
            ->asPost()
            ->secondAction('@redirectTo')
            ->setValue([
                'invoice_id' => $this->resource->entityId(),
            ]);
    }

    protected function initialize(): void
    {
        if (policy_check(InvoicePolicy::class, 'cancelOutdatedInvoicesWithoutRefreshing', $this->resource)) {
            $this->addBasic()
                ->addFields(
                    Builder::typography('description')
                        ->plainText(__p('featured::phrase.cancel_outdated_invoices_without_refreshing_description')),
                );

            $this->addFooter()
                ->addFields(
                    Builder::submit()
                        ->label(__p('core::phrase.submit')),
                    Builder::cancelButton(),
                );

            return;
        }

        $basic = $this->addBasic();

        /**
         * Check if cancel this invoice and create new one
         */
        if (policy_check(InvoicePolicy::class, 'refresh', $this->resource)) {
            $price = $this->resource->package->getPriceByCurrency($this->resource->currency);

            $basic->addField(
                Builder::typography('description')
                    ->plainText(__p('featured::phrase.change_invoice_description', [
                        'price' => Feature::getPriceFormatted($price, $this->resource->currency),
                    ]))
            );
        }

        parent::initialize();
    }

    public function boot(?int $id = null): void
    {
        $this->resource = resolve(InvoiceRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(InvoicePolicy::class, 'prepayment', $context, $this->resource);

        if (MetaFox::isMobile()){
            return;
        }

        $this->processMultiStepForm();
    }

    protected function processMultiStepForm(): void
    {
        if (!Feature::isUsingMultiStepFormForEwallet()) {
            return;
        }

        $actionMeta = $this->getActionMeta();

        $this->setMultiStepFormMeta($actionMeta->toArray());
    }

    protected function getGatewayParams(): array
    {
        return array_merge(parent::getGatewayParams(), [
            'price' => $this->resource->price,
            'currency_id' => $this->resource->currency,
        ]);
    }

    protected function requiredGateway(): bool
    {
        return true;
    }
}
