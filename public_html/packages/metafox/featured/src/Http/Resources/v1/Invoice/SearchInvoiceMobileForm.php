<?php

namespace MetaFox\Featured\Http\Resources\v1\Invoice;

use MetaFox\Featured\Facades\Feature;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Yup\Yup;

class SearchInvoiceMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('featured::phrase.search_invoices'))
            ->action('featured/invoice')
            ->acceptPageParams(['item_type', 'package_id', 'status', 'payment_gateway', 'q', 'from_date', 'to_date', 'transaction_id'])
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
                Builder::choice('status')
                    ->label(__p('core::phrase.status'))
                    ->options(Feature::getSearchInvoiceStatusOptions())
                    ->autoSubmit(),
                Builder::choice('payment_gateway')
                    ->label(__p('payment::admin.payment_gateway'))
                    ->options(Feature::getGatewaySearchOptions())
                    ->autoSubmit(),
                Builder::text('transaction_id')
                    ->label(__p('featured::phrase.transaction_id'))
                    ->maxLength(255),
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
                    ->setAttribute('endOfDay', true)
                    ->autoSubmit()
                    ->yup(Yup::date()
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
                ->targets(['item_type', 'package_id', 'status', 'payment_gateway', 'from_date', 'to_date']),
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
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->options(Feature::getSearchInvoiceStatusOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::choice('payment_gateway')
                ->label(__p('payment::admin.payment_gateway'))
                ->options(Feature::getGatewaySearchOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::dateTime('from_date')
                ->forBottomSheetForm()
                ->label(__p('core::web.from'))
                ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->datePickerMode('date')
                ->setAttribute('startOfDay', true)
                ->autoSubmit()
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::dateTime('to_date')
                ->forBottomSheetForm()
                ->label(__p('core::phrase.to_label'))
                ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->setAttribute('endOfDay', true)
                ->datePickerMode('date')
                ->autoSubmit()
                ->yup(Yup::date()
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
                ->targets(['item_type', 'package_id', 'status', 'payment_gateway', 'from_date', 'to_date'])
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
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->options(Feature::getSearchInvoiceStatusOptions())
                ->forBottomSheetForm()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit(),
            Builder::choice('payment_gateway')
                ->forBottomSheetForm()
                ->label(__p('payment::admin.payment_gateway'))
                ->options(Feature::getGatewaySearchOptions())
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit(),
            Builder::text('transaction_id')
                ->forBottomSheetForm()
                ->label(__p('featured::phrase.transaction_id'))
                ->maxLength(255)
                ->variant('standard')
                ->showWhen(['truthy', 'filters']),
            Builder::dateTime('from_date')
                ->forBottomSheetForm()
                ->label(__p('core::web.from'))
                ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->datePickerMode('date')
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit()
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::dateTime('to_date')
                ->forBottomSheetForm()
                ->label(__p('core::phrase.to_label'))
                ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->datePickerMode('date')
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->autoSubmit()
                ->yup(Yup::date()
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
