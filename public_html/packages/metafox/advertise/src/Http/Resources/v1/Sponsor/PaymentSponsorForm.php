<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Advertise\Models\Sponsor as Model;
use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Form\Builder as Builder;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\MetaFox;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class PaymentSponsorForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class PaymentSponsorForm extends GatewayForm
{
    /**
     * @var bool
     */
    protected bool $isChangePrice = false;

    /**
     * @var float|null
     */
    protected ?float $newPrice = null;

    protected function prepare(): void
    {
        $this->title(__p('payment::phrase.select_payment_gateway'))
            ->action($this->isChangePrice ? 'advertise/invoice/change' : 'advertise/invoice/payment')
            ->asPost()
            ->setValue([
                'item_id'    => $this->resource->entityId(),
                'item_type'  => $this->resource->entityType(),
                'invoice_id' => $this->resource->latestUnpaidInvoice?->entityId(),
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

        $userCurrencyId = app('currency')->getUserCurrencyId($context);

        $item     = $this->resource->item;

        $latestInvoiceCurrencyId = $this->resource->latestUnpaidInvoice?->currency_id ?? $userCurrencyId;

        if (!$item instanceof Content) {
            throw new AuthorizationException();
        }

        $price = resolve(SponsorSettingServiceInterface::class)->getPriceForPayment($context, $item, $latestInvoiceCurrencyId);

        if (!is_numeric($price)) {
            throw new AuthorizationException();
        }

        $price = Support::calculateSponsorPrice($this->resource, $price);

        $this->newPrice = $price;

        $this->addBasic()
            ->addFields(
                Builder::typography('description')
                    ->plainText($this->resource->getChangePriceMessage($price, $latestInvoiceCurrencyId))
            );
    }

    protected function getGatewayParams(): array
    {
        return array_merge(parent::getGatewayParams(), [
            'price' => $this->newPrice ?? $this->resource->latestUnpaidInvoice->price,
        ]);
    }

    public function boot(?int $id = null): void
    {
        $this->resource = resolve(SponsorRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(SponsorPolicy::class, 'payment', $context, $this->resource);

        $this->isChangePrice = Support::isSponsorChangePrice($this->resource);

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

    protected function requiredGateway(): bool
    {
        return true;
    }
}
