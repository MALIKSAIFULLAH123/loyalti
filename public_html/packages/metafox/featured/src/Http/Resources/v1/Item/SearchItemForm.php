<?php

namespace MetaFox\Featured\Http\Resources\v1\Item;

use Carbon\Carbon;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

class SearchItemForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('featured/item')
            ->acceptPageParams(['item_type', 'package_id', 'status', 'package_duration_period', 'from_date', 'to_date', 'pricing'])
            ->asGet()
            ->setValue([
                'from_date' => null,
                'to_date' => null,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset'])
            ->addFields(
                Builder::choice('item_type')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.item_type'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->options(Feature::getApplicableItemTypeOptions()),
                Builder::choice('package_id')
                    ->forAdminSearchForm()
                    ->label(__p('featured::phrase.package'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->options(Feature::getPackageSearchOptions()),
                Builder::choice('pricing')
                    ->forAdminSearchForm()
                    ->label(__p('featured::web.pricing'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->options(Feature::getPricingOptions()),
                Builder::choice('status')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.status'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->options(Feature::getSearchItemStatusOptions()),
                Builder::choice('package_duration_period')
                    ->forAdminSearchForm()
                    ->label(__p('featured::admin.duration'))
                    ->sxFieldWrapper(Feature::getSearchFormResponsiveSx())
                    ->options(Feature::getDurationOptionsForSearch()),
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
                    ->label(__p('core::phrase.submit'))
                    ->forAdminSearchForm(),
                Builder::clearSearchForm()->marginDense(),
            );
    }
}
