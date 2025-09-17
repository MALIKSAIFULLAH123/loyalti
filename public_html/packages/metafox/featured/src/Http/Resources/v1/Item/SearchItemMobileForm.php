<?php

namespace MetaFox\Featured\Http\Resources\v1\Item;

use MetaFox\Featured\Facades\Feature;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Yup\Yup;

class SearchItemMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('featured::phrase.search_items'))
            ->action('featured/item')
            ->acceptPageParams(['item_type', 'package_id', 'status', 'package_duration_period', 'from_date', 'to_date', 'pricing'])
            ->asGet()
            ->setValue([
                'from_date' => null,
                'to_date'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()->asHorizontal()
            ->addFields(
                Builder::choice('item_type')
                    ->label(__p('core::phrase.item_type'))
                    ->options(Feature::getApplicableItemTypeOptions())
                    ->autoSubmit(),
                Builder::choice('package_id')
                    ->label(__p('featured::phrase.package'))
                    ->options(Feature::getPackageSearchOptions())
                    ->autoSubmit(),
                Builder::choice('pricing')
                    ->label(__p('featured::web.pricing'))
                    ->options(Feature::getPricingOptions())
                    ->autoSubmit(),
                Builder::choice('status')
                    ->label(__p('core::phrase.status'))
                    ->options(Feature::getSearchItemStatusOptions())
                    ->autoSubmit(),
                Builder::choice('package_duration_period')
                    ->label(__p('featured::admin.duration'))
                    ->options(Feature::getDurationOptionsForSearch())
                    ->autoSubmit(),
                Builder::dateTime('from_date')
                    ->label(__p('core::web.from'))
                    ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                    ->setAttribute('startOfDay', true)
                    ->datePickerMode('date')
                    ->autoSubmit()
                    ->yup(
                        Yup::date()->nullable()
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                    ),
                Builder::dateTime('to_date')
                    ->label(__p('core::phrase.to_label'))
                    ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                    ->datePickerMode('date')
                    ->autoSubmit()
                    ->setAttribute('endOfDay', true)
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->min(['ref' => 'from_date'])
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                            ->setError('min', __p('featured::validation.to_date_should_be_greater_than_the_to_date'))
                            ->setError(
                                'minDateTime',
                                __p('featured::validation.to_date_should_be_greater_than_the_to_date')
                            ))
            );
    }

    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);

        $basic->addFields(
            $this->getClearSearchFieldsFlatten()
                ->targets(['item_type', 'package_id', 'status', 'package_duration_period', 'from_date', 'to_date', 'pricing']),
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::choice('item_type')
                ->label(__p('core::phrase.item_type'))
                ->options(Feature::getApplicableItemTypeOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::autocomplete('package_id')
                ->label(__p('featured::phrase.package'))
                ->useOptionContext()
                ->forBottomSheetForm()
                ->searchEndpoint('/featured/package')
                ->searchParams([
                    'view' => Browse::VIEW_SEARCH,
                ])
                ->autoSubmit(),
            Builder::choice('pricing')
                ->label(__p('featured::web.pricing'))
                ->options(Feature::getPricingOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->options(Feature::getSearchItemStatusOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::choice('package_duration_period')
                ->label(__p('featured::admin.duration'))
                ->options(Feature::getDurationOptionsForSearch())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::dateTime('from_date')
                ->label(__p('core::web.from'))
                ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->datePickerMode('date')
                ->setAttribute('startOfDay', true)
                ->forBottomSheetForm()
                ->autoSubmit()
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::dateTime('to_date')
                ->label(__p('core::phrase.to_label'))
                ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->datePickerMode('date')
                ->setAttribute('endOfDay', true)
                ->forBottomSheetForm()
                ->autoSubmit()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                        ->setError('min', __p('featured::validation.to_date_should_be_greater_than_the_to_date'))
                        ->setError(
                            'minDateTime',
                            __p('featured::validation.to_date_should_be_greater_than_the_to_date')
                        ))
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['item_type', 'package_id', 'status', 'package_duration_period', 'from_date', 'to_date'])
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters']),
            Builder::choice('item_type')
                ->label(__p('core::phrase.item_type'))
                ->options(Feature::getApplicableItemTypeOptions())
                ->forBottomSheetForm()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit(),
            Builder::choice('package_id')
                ->label(__p('featured::phrase.package'))
                ->options(Feature::getPackageSearchOptions())
                ->forBottomSheetForm()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit(),
            Builder::choice('pricing')
                ->label(__p('featured::web.pricing'))
                ->options(Feature::getPricingOptions())
                ->forBottomSheetForm()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit(),
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->options(Feature::getSearchItemStatusOptions())
                ->forBottomSheetForm()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit(),
            Builder::choice('package_duration_period')
                ->label(__p('featured::admin.duration'))
                ->options(Feature::getDurationOptionsForSearch())
                ->forBottomSheetForm()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit(),
            Builder::dateTime('from_date')
                ->label(__p('core::web.from'))
                ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->datePickerMode('date')
                ->forBottomSheetForm()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit()
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::dateTime('to_date')
                ->label(__p('core::phrase.to_label'))
                ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->datePickerMode('date')
                ->forBottomSheetForm()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                        ->setError('min', __p('featured::validation.to_date_should_be_greater_than_the_to_date'))
                        ->setError(
                            'minDateTime',
                            __p('featured::validation.to_date_should_be_greater_than_the_to_date')
                        ))
        );
    }
}
