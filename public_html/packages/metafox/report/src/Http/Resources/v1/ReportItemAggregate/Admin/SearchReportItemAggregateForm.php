<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItemAggregate\Admin;

use Illuminate\Support\Str;
use MetaFox\Form\AbstractForm;
use MetaFox\Report\Repositories\ReportItemAggregateAdminRepositoryInterface;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Report\Models\ReportItemAggregate as Model;

/**
 * Class SearchReportItemAggregateForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchReportItemAggregateForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action(apiUrl('admin.report.items.index'))
            ->acceptPageParams(['q', 'item_type'])
            ->asGet()
            ->setValue([
                'item_type' => '',
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();

        $itemTypeOptions = $this->getItemTypeOptions();

        $basic->addFields(
            Builder::choice('item_type')
                ->label(__p('core::phrase.item_type'))
                ->options($itemTypeOptions)
                ->forAdminSearchForm(),
        );

        if (!empty($itemTypeOptions)) {
            $basic->addField(Builder::submit()->forAdminSearchForm());
        }
    }

    /**
     * @return array<int, mixed>
     */
    protected function getItemTypeOptions(): array
    {
        return $this->getAggregateRepository()
            ->getModel()
            ->newModelQuery()
            ->groupBy('item_type')
            ->get(['item_type'])
            ->collect()
            ->pluck('item_type')
            ->map(function (string $itemType) {
                return [
                    'label' => Str::headline(__p_type_key($itemType)),
                    'value' => $itemType,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function getAggregateRepository(): ReportItemAggregateAdminRepositoryInterface
    {
        return resolve(ReportItemAggregateAdminRepositoryInterface::class);
    }
}
