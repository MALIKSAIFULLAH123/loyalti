<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointPackage;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PointPackage as Model;
use MetaFox\ActivityPoint\Policies\PackagePolicy;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
use MetaFox\App\Models\Package;
use MetaFox\App\Repositories\PackageRepositoryInterface;
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
class PurchasePointPackageForm extends GatewayForm
{
    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(?int $id = null): void
    {
        $this->resource = resolve(PointPackageRepositoryInterface::class)->find($id);

        policy_authorize(PackagePolicy::class, 'purchase', user(), $this->resource);

        if (MetaFox::isMobile()){
            return;
        }

        $this->processMultiStepFormMeta();
    }

    protected function processMultiStepFormMeta(): void
    {
        /**
         * @var Package $app
         */
        $app = resolve(PackageRepositoryInterface::class)->getPackageByName('metafox/emoney');

        if (version_compare($app->version, '5.1.5', '<')) {
            return;
        }

        $actionMeta = $this->getActionMeta();

        $this->setMultiStepFormMeta($actionMeta->toArray());
    }

    protected function prepare(): void
    {
        $this->title(__p('activitypoint::phrase.select_payment_gateway'))
            ->action(apiUrl('activitypoint.package.purchase', ['id' => $this->resource->entityId()]))
            ->secondAction(MetaFoxForm::FORM_ACTION_REDIRECT_TO)
            ->setAttribute('keepPaginationData', true)
            ->asPost()
            ->setValue([]);
    }

    protected function getGatewayParams(): array
    {
        $context = user();

        $currency = app('currency')->getUserCurrencyId($context);

        $price = Arr::get($this->resource->price, $currency, Arr::first($this->resource->price));

        return array_merge(parent::getGatewayParams(), [
            'price' => $price,
        ]);
    }
}
