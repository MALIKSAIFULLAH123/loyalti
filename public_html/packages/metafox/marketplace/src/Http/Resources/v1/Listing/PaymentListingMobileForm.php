<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Marketplace\Policies\InvoicePolicy;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Marketplace\Support\Facade\Listing;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;

class PaymentListingMobileForm extends GatewayForm
{
    public function boot(?int $id = null): void
    {
        $this->resource = resolve(ListingRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(InvoicePolicy::class, 'beforePayment', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $this->title(__p('payment::phrase.select_payment_gateway'))
            ->action('marketplace-invoice')
            ->secondAction('@redirectTo')
            ->asPost()
            ->setValue([
                'id' => $this->resource->entityId(),
            ]);
    }

    protected function addMoreBasicFields(Section $basic): void
    {
        parent::addMoreBasicFields($basic);

        $basic->addFields(
            Builder::hidden('id'),
        );
    }

    protected function getGatewayParams(): array
    {
        $context = user();

        $price = Listing::getUserPrice($context, $this->resource->price);

        if (!$price) {
            $price = 0;
        }

        return array_merge(parent::getGatewayParams(), [
            'payee_id' => $this->resource->userId(),
            'price'    => $price,
        ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        parent::initialize();

        $this->addHeader(['showRightHeader' => false])
            ->component('FormHeader')
            ->autoSubmit(false);
    }
}
