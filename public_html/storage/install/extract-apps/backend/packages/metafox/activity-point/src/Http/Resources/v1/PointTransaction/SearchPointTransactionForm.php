<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction;

use MetaFox\ActivityPoint\Models\PointTransaction as Model;
use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\ActivityPoint\Support\Browse\Traits\DateFieldForSearchFromTrait;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchPointTransactionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName activitypoint_transaction.search
 * @driverType form
 * @preload    1
 */
class SearchPointTransactionForm extends AbstractForm
{
    use DateFieldForSearchFromTrait;

    protected function prepare(): void
    {
        $this->action(apiUrl('activitypoint.transaction.index'))
            ->acceptPageParams(['type', 'from', 'to', 'sort', 'sort_type', 'limit'])
            ->setValue([
                'type' => ActivityPoint::TYPE_ALL,
                'from' => null,
                'to'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::choice('type')
                ->options($this->getTransactionTypes())
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->label(__p('activitypoint::phrase.transaction_type')),
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

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getTransactionTypes(): array
    {
        $types = ActivityPoint::ALLOW_TYPES;

        return collect($types)->map(function ($value, $key) {
            return [
                'label' => __p($key),
                'value' => $value,
            ];
        })->values()->toArray();
    }
}
