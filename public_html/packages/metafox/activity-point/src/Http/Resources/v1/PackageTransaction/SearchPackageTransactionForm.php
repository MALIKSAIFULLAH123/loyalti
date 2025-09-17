<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction;

use Carbon\Carbon;
use MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase\StatusScope;
use MetaFox\ActivityPoint\Support\Browse\Traits\DateFieldForSearchFromTrait;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Payment\Models\Order as Model;

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
        $this->action(apiUrl('activitypoint.package-transaction.index'))
            ->acceptPageParams(['q', 'status', 'from', 'to', 'transaction_id', 'sort', 'sort_type', 'limit'])
            ->setValue([
                'q'      => '',
                'status' => Model::STATUS_ALL,
                'to'     => Carbon::now(),
                'from'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->placeholder(__p('activitypoint::phrase.enter_package_name')),
            Builder::choice('status')
                ->options(StatusScope::getStatusOptions())
                ->disableClearable()
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->label(__p('activitypoint::phrase.payment_status')),
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
}
