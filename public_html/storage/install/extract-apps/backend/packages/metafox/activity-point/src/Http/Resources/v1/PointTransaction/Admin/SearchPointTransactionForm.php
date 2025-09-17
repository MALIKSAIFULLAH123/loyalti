<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction\Admin;

use Carbon\Carbon;
use MetaFox\ActivityPoint\Models\PointTransaction as Model;
use MetaFox\ActivityPoint\Repositories\PointTransactionRepositoryInterface;
use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\ActivityPoint\Support\Browse\Traits\DateFieldForSearchFromTrait;
use MetaFox\ActivityPoint\Support\Facade\ActionType;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;

/**
 * Class SearchPointTransactionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName activitypoint_transaction.search.admin
 * @driverType form
 */
class SearchPointTransactionForm extends AbstractForm
{
    use DateFieldForSearchFromTrait;

    protected function prepare(): void
    {
        $this->action('/activitypoint/transaction')
            ->acceptPageParams(['q', 'type', 'from', 'to', 'sort', 'sort_type', 'page', 'limit', 'package_id', 'action_id'])
            ->setValue([
                'q'    => '',
                'type' => ActivityPoint::TYPE_ALL,
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
            Builder::text('q')
                ->forAdminSearchForm()
                ->placeholder(__p('activitypoint::phrase.enter_member_name')),
            $this->buildFromField(),
            $this->buildToField(),
            Builder::choice('type')
                ->forAdminSearchForm()
                ->options($this->getTransactionTypes())
                ->label(__p('activitypoint::phrase.point_source')),
            Builder::selectPackage('package_id')
                ->forAdminSearchForm()
                ->options($this->getPackageOptions()),
            Builder::choice('action_id')
                ->forAdminSearchForm()
                ->options(ActionType::getActionTypeOptions())
                ->label(__p('activitypoint::phrase.action_type')),
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

    /**
     * @return array<int, mixed>
     */
    public function getPackageOptions(): array
    {
        $data = [
            [
                'label' => __p('core::phrase.all'),
                'value' => 'all',
            ],
        ];

        return array_merge($data, resolve(PointTransactionRepositoryInterface::class)->getPackageOptions());
    }
}
