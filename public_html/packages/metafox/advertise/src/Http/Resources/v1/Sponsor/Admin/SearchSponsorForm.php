<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor\Admin;

use Illuminate\Support\Carbon;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;

class SearchSponsorForm extends AbstractForm
{
    public function prepare(): void
    {
        $this->asGet()
            ->action('/advertise/sponsor')
            ->acceptPageParams(['start_date', 'end_date', 'title', 'full_name', 'status', 'sponsor_type', 'is_active'])
            ->submitAction('@formAdmin/search/SUBMIT')
            ->setValue([
                'start_date' => null,
                'end_date'   => null,
            ]);
    }

    public function initialize(): void
    {
        $this->addBasic([
            'sx' => [
                'flexFlow'   => 'wrap',
                'alignItems' => 'flex-start',
            ],
        ])->asHorizontal()
            ->addFields(
                Builder::date('start_date')
                    ->label(__p('advertise::phrase.start_date'))
                    ->startOfDay()
                    ->forAdminSearchForm(),
                Builder::date('end_date')
                    ->label(__p('advertise::phrase.end_date'))
                    ->endOfDay()
                    ->forAdminSearchForm(),
                Builder::text('title')
                    ->forAdminSearchForm()
                    ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                    ->label(__p('core::phrase.title'))
                    ->placeholder(__p('core::phrase.title')),
                Builder::choice('status')
                    ->label(__p('core::web.status'))
                    ->options($this->getStatusOptions())
                    ->forAdminSearchForm(),
                Builder::text('full_name')
                    ->forAdminSearchForm()
                    ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                    ->placeholder(__p('advertise::phrase.creator'))
                    ->label(__p('advertise::phrase.creator')),
                Builder::choice('sponsor_type')
                    ->label(__p('advertise::phrase.sponsor_type'))
                    ->options(Support::getSponsorTypeOptions())
                    ->forAdminSearchForm(),
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
}
