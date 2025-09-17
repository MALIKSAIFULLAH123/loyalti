<?php

namespace MetaFox\Featured\Http\Resources\v1\Transaction\Admin;

use Carbon\Carbon;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

class SearchTransactionForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/featured/transaction')
            ->acceptPageParams(['full_name', 'item_type', 'status', 'payment_gateway', 'transaction_id', 'from_date', 'to_date'])
            ->asGet()
            ->submitAction('@formAdmin/search/SUBMIT')
            ->setValue([
                'from_date' => null,
                'to_date'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic([
            'sx' => [
                'flexFlow'   => 'wrap',
                'alignItems' => 'flex-start',
            ],
        ])
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset'])
            ->addFields(
                Builder::text('full_name')
                    ->forAdminSearchForm()
                    ->label(__p('featured::admin.user'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->maxLength(255),
                Builder::choice('item_type')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.item_type'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->options(Feature::getApplicableItemTypeOptions()),
                Builder::choice('status')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.status'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->options(Feature::getTransactionStatusSearchOptions()),
                Builder::choice('payment_gateway')
                    ->forAdminSearchForm()
                    ->label(__p('payment::admin.payment_gateway'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->options(Feature::getGatewaySearchOptions()),
                Builder::text('transaction_id')
                    ->forAdminSearchForm()
                    ->label(__p('featured::phrase.transaction_id'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->maxLength(255),
                Builder::date('from_date')
                    ->forAdminSearchForm()
                    ->label(__p('core::web.from'))
                    ->startOfDay()
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->maxDate(Carbon::now()->toISOString())
                    ->yup(
                        Yup::date()->nullable()
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                    ),
                Builder::date('to_date')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.to_label'))
                    ->endOfDay()
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->maxDate(Carbon::now()->toISOString())
                    ->yup(Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                        ->setError('min', __p('featured::validation.to_date_should_be_greater_than_the_to_date'))
                        ->setError(
                            'minDateTime',
                            __p('featured::validation.to_date_should_be_greater_than_the_to_date')
                        )),
                Builder::submit()
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.search')),
                Builder::clearSearchForm()
                    ->align('center')
                    ->forAdminSearchForm()
                    ->sizeMedium()
                    ->label(__p('core::phrase.reset')),
            );
    }
}
