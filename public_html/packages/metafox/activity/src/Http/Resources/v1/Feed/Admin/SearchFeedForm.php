<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed\Admin;

use Carbon\Carbon;
use MetaFox\Activity\Repositories\TypeRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

class SearchFeedForm extends AbstractForm
{
    public function prepare(): void
    {
        $this->asGet()
            ->action('/feed/feed')
            ->acceptPageParams(['q', 'user_name', 'owner_name', 'item_type', 'type_id', 'from_date', 'to_date'])
            ->setValue([
                'from_date' => null,
                'to_date'   => Carbon::now(),
            ]);
    }

    public function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.content_label')),
            Builder::text('user_name')
                ->forAdminSearchForm()
                ->label(__p('activity::phrase.posted_by')),
            Builder::text('owner_name')
                ->forAdminSearchForm()
                ->label(__p('activity::phrase.posted_to')),
            Builder::choice('item_type')
                ->label(__p('activity::phrase.item'))
                ->options($this->getEntityTypeOptions())
                ->forAdminSearchForm(),
            Builder::choice('type_id')
                ->label(__p('activity::phrase.feed_type'))
                ->forAdminSearchForm()
                ->options($this->getTypeOptions()),
            Builder::date('from_date')
                ->label(__p('core::web.from'))
                ->startOfDay()
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->maxDate(Carbon::now()->toISOString())
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::date('to_date')
                ->label(__p('core::phrase.to_label'))
                ->endOfDay()
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->maxDate(Carbon::now()->toISOString())
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                        ->setError('min', __p('activity::validation.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError(
                            'minDateTime',
                            __p('activity::validation.the_end_time_should_be_greater_than_the_current_time')
                        )
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.search')),
                Builder::clearSearchForm()
                    ->label(__p('core::phrase.reset'))
                    ->align('center'),
            );
    }

    protected function getTypeOptions(): array
    {
        return resolve(TypeRepositoryInterface::class)->getActiveTypeOptions();
    }

    protected function getEntityTypeOptions(): array
    {
        return resolve(TypeRepositoryInterface::class)->getActiveEntityTypeOptions();
    }

    protected function getResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '50%',
                'md' => '220px',
            ],
            'width' => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }
}
