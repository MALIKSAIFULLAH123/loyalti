<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise\Admin;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

class SearchAdvertiseForm extends AbstractForm
{
    public function prepare(): void
    {
        $this->title('')
            ->asGet()
            ->action('/advertise/advertise')
            ->acceptPageParams(['placement_id', 'start_date', 'end_date', 'title', 'full_name', 'status', 'is_active'])
            ->submitAction('@formAdmin/search/SUBMIT')
            ->setValue([
                'start_date' => null,
                'end_date'   => null,
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    public function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('title')
                ->forAdminSearchForm()
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('core::phrase.title')),
            Builder::choice('placement_id')
                ->label(__p('advertise::phrase.placement'))
                ->options($this->getPlacementOptions())
                ->forAdminSearchForm(),
            Builder::date('start_date')
                ->label(__p('advertise::phrase.start_date'))
                ->startOfDay()
                ->forAdminSearchForm()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('advertise::phrase.start_date')]))
                ),
            Builder::date('end_date')
                ->label(__p('advertise::phrase.end_date'))
                ->endOfDay()
                ->forAdminSearchForm()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'start_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('advertise::phrase.to_ucfirst')]))
                        ->setError('min', __p('advertise::phrase.the_end_time_should_be_greater_than_the_start_time'))
                ),
            Builder::choice('status')
                ->label(__p('core::web.status'))
                ->options($this->getStatusOptions())
                ->forAdminSearchForm(),
            Builder::text('full_name')
                ->forAdminSearchForm()
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                ->placeholder(__p('advertise::phrase.creator'))
                ->label(__p('advertise::phrase.creator')),
            Builder::choice('is_active')
                ->label(__p('core::phrase.is_active'))
                ->options($this->getActiveOptions())
                ->forAdminSearchForm(),
        );

        $this->addFooter([
            'sx' => [
                'flexFlow'   => 'wrap',
                'alignItems' => 'center',
            ],
        ])
            ->asHorizontal()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.search')),
                Builder::clearSearchForm()
                    ->label(__p('core::phrase.reset'))
                    ->align('center'),
            );
    }

    protected function getActiveOptions(): array
    {
        return Facade::getActiveOptions();
    }

    protected function getStatusOptions(): array
    {
        return Facade::getAdvertiseStatusOptions();
    }

    /**
     * @throws AuthenticationException
     */
    protected function getPlacementOptions(): array
    {
        $collection = collect(Facade::getPlacementOptions(user()));

        $collection = $collection->transform(function (array $item) {
            return collect($item)->except(['description', 'placement_type']);
        })->all();

        return array_values($collection);
    }
}
