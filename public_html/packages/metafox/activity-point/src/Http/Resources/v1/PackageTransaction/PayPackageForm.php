<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PackagePurchase as Model;
use MetaFox\ActivityPoint\Policies\PackagePurchasePolicy;
use MetaFox\ActivityPoint\Repositories\PurchasePackageRepositoryInterface;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;
use MetaFox\Platform\MetaFox;

/**
 * Class PurchasePointPackageForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName activitypoint_package.purchase
 * @driverType form
 */
class PayPackageForm extends GatewayForm
{
    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(?int $id = null): void
    {
        $this->resource = resolve(PurchasePackageRepositoryInterface::class)->find($id);

        if (MetaFox::isMobile()) {
            return;
        }

        policy_authorize(PackagePurchasePolicy::class, 'pay', user(), $this->resource);

        $this->processMultiStepFormMeta();
    }

    protected function processMultiStepFormMeta(): void
    {
        $actionMeta = $this->getActionMeta();

        $this->setMultiStepFormMeta($actionMeta->toArray());
    }

    protected function prepare(): void
    {
        $this->title(__p('activitypoint::phrase.select_payment_gateway'))
            ->action(apiUrl('activitypoint.package-transaction.payment', ['id' => $this->resource->entityId()]))
            ->secondAction(MetaFoxForm::FORM_ACTION_REDIRECT_TO)
            ->setAttribute('keepPaginationData', true)
            ->asPost()
            ->setValue([]);
    }

    protected function getGatewayParams(): array
    {
        $context = user();

        $currency = app('currency')->getUserCurrencyId($context);
        $package  = $this->resource->package;

        $price = Arr::get($package->price, $currency, Arr::first($package->price));

        return array_merge(parent::getGatewayParams(), [
            'price' => $price,
        ]);
    }
}
