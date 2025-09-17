<?php

namespace MetaFox\Core\Http\Resources\v1\StatsContentType\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Core\Models\StatsContentType as Model;
use MetaFox\Core\Repositories\StatsContentTypeAdminRepositoryInterface;

/**
 * Class EdiStatsContentTypeForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditStatsContentTypeForm extends AbstractForm
{
    public function boot(StatsContentTypeAdminRepositoryInterface $repository, int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit_stats_type_title'))
            ->action(apiUrl('admin.statistic.type.update', ['type' => $this->resource->entityId()]))
            ->asPatch()
            ->setValue([
                'icon'  => $this->resource->icon,
                'label' => $this->resource->latestStatistic?->label ?? '',
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('label')
                    ->label(__p('core::phrase.label'))
                    ->disabled(),
                Builder::text('icon')
                    ->component('IconPicker')
                    ->required(true)
                    ->label(__p('app::phrase.icon'))
                    ->maxLength(255)
                    ->yup(Yup::string()->required()),
            );

        $this->addDefaultFooter();
    }
}
