<?php

namespace MetaFox\Advertise\Http\Resources\v1\Invoice;

use Illuminate\Support\Arr;
use MetaFox\Advertise\Models\Invoice as Model;
use MetaFox\Advertise\Policies\InvoicePolicy;
use MetaFox\Advertise\Repositories\InvoiceRepositoryInterface;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Form\Builder as Builder;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;
use MetaFox\Platform\MetaFox;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class PaymentInvoiceForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class PaymentInvoiceForm extends GatewayForm
{
    protected bool $isChangePrice = false;
    protected function prepare(): void
    {
        $this->title(__p('payment::phrase.select_payment_gateway'))
            ->action($this->isChangePrice ? 'advertise/invoice/change' : 'advertise/invoice/payment')
            ->asPost()
            ->setValue([
                'item_id'    => $this->resource->itemId(),
                'item_type'  => $this->resource->itemType(),
                'invoice_id' => $this->resource->entityId(),
            ]);

        match ($this->isChangePrice) {
            true => $this->title(__p('advertise::phrase.change_invoice'))
                ->secondAction('@redirectTo'),
            false => $this->secondAction('@redirectTo'),
        };
    }

    protected function initialize(): void
    {
        if ($this->isChangePrice) {
            $this->addChangedPriceFields();
        }

        parent::initialize();
    }

    protected function addChangedPriceFields(): void
    {
        $context = user();

        $data = $this->resource->item->toPayment($context);

        if (!count($data)) {
            abort(403);
        }

        $this->addBasic()
            ->addFields(
                Builder::typography('description')
                    ->plainText($this->resource->item->getChangePriceMessage(Arr::get($data, 'price'), Arr::get($data, 'currency_id')))
            );
    }

    public function boot(?int $id = null): void
    {
        $this->resource = resolve(InvoiceRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(InvoicePolicy::class, 'prepayment', $context, $this->resource);

        $this->isChangePrice = $this->resource->item->isPriceChanged($this->resource);

        if (MetaFox::isMobile()){
            return;
        }

        $this->processMultiStepForm();
    }

    protected function processMultiStepForm(): void
    {
        if (!Support::isUsingMultiStepFormForEwallet()) {
            return;
        }

        $actionMeta = $this->getActionMeta();

        $this->setMultiStepFormMeta($actionMeta->toArray());
    }

    protected function getGatewayParams(): array
    {
        return array_merge(parent::getGatewayParams(), [
            'price' => $this->resource->price,
        ]);
    }

    protected function requiredGateway(): bool
    {
        return true;
    }
}
