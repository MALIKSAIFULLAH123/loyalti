<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction\Admin;

use Carbon\Carbon;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
use MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase\StatusScope;
use MetaFox\ActivityPoint\Support\Browse\Traits\DateFieldForSearchFromTrait;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Payment\Models\Order as Model;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchPackageTransactionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName activitypoint_transaction.search
 * @driverType form
 * @preload    1
 */
class SearchPackageTransactionForm extends AbstractForm
{
    use DateFieldForSearchFromTrait;

    protected function prepare(): void
    {
        $this->action(apiUrl('admin.activitypoint.package-transaction.index'))
            ->acceptPageParams(['full_name', 'status', 'from', 'to', 'gateway_id', 'transaction_id', 'sort', 'sort_type', 'limit', 'package_id'])
            ->setValue([
                'type' => Model::STATUS_ALL,
                'to'   => Carbon::now(),
                'from' => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('full_name')
                ->forAdminSearchForm()
                ->placeholder(__p('activitypoint::phrase.enter_member_name')),
            Builder::choice('package_id')
                ->options($this->getPackageOptions())
                ->forAdminSearchForm()
                ->label(__p('activitypoint::phrase.package')),
            Builder::choice('status')
                ->options(StatusScope::getStatusOptions())
                ->forAdminSearchForm()
                ->label(__p('activitypoint::phrase.payment_status')),
            Builder::choice('gateway_id')
                ->forAdminSearchForm()
                ->label(__p('payment::admin.payment_gateway'))
                ->options($this->getGatewayOptions()),
            Builder::text('transaction_id')
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->label(__p('payment::phrase.transaction_id')),
            $this->buildFromField(),
            $this->buildToField(),
            Builder::submit()
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->forAdminSearchForm()
                ->sizeMedium(),
        );
    }

    protected function getPackageOptions(): array
    {
        return resolve(PointPackageRepositoryInterface::class)->getSearchOptions();
    }

    private function getGatewayOptions(): array
    {
        return resolve(GatewayRepositoryInterface::class)->getGatewaySearchOptions();
    }
}
