<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Policies\AdvertisePolicy;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;
use MetaFox\Advertise\Support\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Yup\Yup;
use MetaFox\Advertise\Models\Advertise as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchChartMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchChartMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $min = $this->getMinDate();
        $max = $this->getMaxDate();

        $this->action('advertise/report/:id')
            ->title(__p('core::phrase.search'))
            ->asGet()
            ->setValue([
                'view'                                        => Support::STATISTIC_VIEW_MONTH,
                'start_date_' . Support::STATISTIC_VIEW_MONTH => $min->format('c'),
                'end_date_' . Support::STATISTIC_VIEW_MONTH   => $max->format('c'),
                'start_date_' . Support::STATISTIC_VIEW_WEEK  => $min->format('c'),
                'end_date_' . Support::STATISTIC_VIEW_WEEK    => $max->format('c'),
                'start_date_' . Support::STATISTIC_VIEW_DAY   => $min->format('c'),
                'end_date_' . Support::STATISTIC_VIEW_DAY     => $max->format('c'),
            ]);
    }

    protected function getViewOptions(): array
    {
        return [
            [
                'value' => Support::STATISTIC_VIEW_DAY,
                'label' => __p('advertise::phrase.day'),
            ],
            [
                'value' => Support::STATISTIC_VIEW_WEEK,
                'label' => __p('advertise::phrase.week'),
            ],
            [
                'value' => Support::STATISTIC_VIEW_MONTH,
                'label' => __p('advertise::phrase.month'),
            ],
        ];
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::choice('view')
                    ->label(__p('advertise::phrase.view_by'))
                    ->placeholder(__p('advertise::phrase.view_by'))
                    ->options($this->getViewOptions()),
                Builder::date('start_date_' . Support::STATISTIC_VIEW_MONTH)
                    ->label(__p('core::web.from'))
                    ->placeholder(__p('core::web.from'))
                    ->minDate($this->getMinDate()->toISOString())
                    ->maxDate($this->getMaxDate()->toISOString())
                    ->views(['month', 'year'])
                    ->showWhen([
                        'eq',
                        'view',
                        Support::STATISTIC_VIEW_MONTH,
                    ])
                    ->yup(
                        Yup::date()
                            ->nullable()
                    ),
                Builder::date('end_date_' . Support::STATISTIC_VIEW_MONTH)
                    ->label(__p('advertise::phrase.to_ucfirst'))
                    ->placeholder(__p('advertise::phrase.to_ucfirst'))
                    ->minDate($this->getMinDate()->toISOString())
                    ->maxDate($this->getMaxDate()->toISOString())
                    ->views(['month', 'year'])
                    ->showWhen([
                        'eq',
                        'view',
                        Support::STATISTIC_VIEW_MONTH,
                    ])
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->min(['ref' => 'start_date_' . Support::STATISTIC_VIEW_MONTH])
                    ),
                Builder::date('start_date_' . Support::STATISTIC_VIEW_WEEK)
                    ->label(__p('core::web.from'))
                    ->placeholder(__p('core::web.from'))
                    ->minDate($this->getMinDate()->toISOString())
                    ->maxDate($this->getMaxDate()->toISOString())
                    ->showWhen([
                        'eq',
                        'view',
                        Support::STATISTIC_VIEW_WEEK,
                    ])
                    ->yup(
                        Yup::date()
                            ->nullable()
                    ),
                Builder::date('end_date_' . Support::STATISTIC_VIEW_WEEK)
                    ->label(__p('advertise::phrase.to_ucfirst'))
                    ->placeholder(__p('advertise::phrase.to_ucfirst'))
                    ->minDate($this->getMinDate()->toISOString())
                    ->maxDate($this->getMaxDate()->toISOString())
                    ->showWhen([
                        'eq',
                        'view',
                        Support::STATISTIC_VIEW_WEEK,
                    ])
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->min(['ref' => 'start_date_' . Support::STATISTIC_VIEW_WEEK])
                    ),
                Builder::date('start_date_' . Support::STATISTIC_VIEW_DAY)
                    ->label(__p('core::web.from'))
                    ->placeholder(__p('core::web.from'))
                    ->minDate($this->getMinDate()->toISOString())
                    ->maxDate($this->getMaxDate()->toISOString())
                    ->showWhen([
                        'eq',
                        'view',
                        Support::STATISTIC_VIEW_DAY,
                    ])
                    ->yup(
                        Yup::date()
                            ->nullable()
                    ),
                Builder::date('end_date_' . Support::STATISTIC_VIEW_DAY)
                    ->label(__p('advertise::phrase.to_ucfirst'))
                    ->placeholder(__p('advertise::phrase.to_ucfirst'))
                    ->minDate($this->getMinDate()->toISOString())
                    ->maxDate($this->getMaxDate()->toISOString())
                    ->showWhen([
                        'eq',
                        'view',
                        Support::STATISTIC_VIEW_DAY,
                    ])
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->min(['ref' => 'start_date_' . Support::STATISTIC_VIEW_DAY])
                    )
            );
    }

    protected function getMinDate(): CarbonInterface
    {
        return Carbon::parse($this->resource->start_date);
    }

    public function getMaxDate(): CarbonInterface
    {
        if ($this->resource->status == Support::ADVERTISE_STATUS_COMPLETED) {
            return Carbon::parse($this->resource->completed_at);
        }

        if (null !== $this->resource->end_date) {
            return Carbon::parse($this->resource->end_date);
        }

        return Carbon::now();
    }

    public function boot(int $id)
    {
        $this->resource = resolve(AdvertiseRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(AdvertisePolicy::class, 'viewReport', $context, $this->resource);
    }
}
