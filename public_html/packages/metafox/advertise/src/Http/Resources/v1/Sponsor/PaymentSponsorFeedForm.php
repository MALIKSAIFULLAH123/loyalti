<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Form\Builder as Builder;
use MetaFox\Advertise\Models\Sponsor as Model;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;
use MetaFox\Platform\Contracts\Content;

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
class PaymentSponsorFeedForm extends GatewayForm
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

        $currencyId = app('currency')->getUserCurrencyId($context);

        $item     = $this->resource->item;
        $currency = $this->resource->latestUnpaidInvoice?->currency_id;

        if (!$item instanceof Content) {
            throw new AuthorizationException();
        }

        $price = resolve(SponsorSettingServiceInterface::class)->getPriceForPayment($context, $item, $currency);

        if (!is_numeric($price)) {
            throw new AuthorizationException();
        }

        $price = Support::calculateSponsorPrice($this->resource, $price);

        $this->newPrice = $price;

        $this->addBasic()
            ->addFields(
                Builder::typography('description')
                    ->plainText($this->resource->getChangePriceMessage($price, $currencyId))
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
    }

    protected function requiredGateway(): bool
    {
        return true;
    }
}
