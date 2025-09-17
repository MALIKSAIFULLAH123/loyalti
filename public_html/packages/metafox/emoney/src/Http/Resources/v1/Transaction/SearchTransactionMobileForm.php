<?php

namespace MetaFox\EMoney\Http\Resources\v1\Transaction;

use Carbon\Carbon;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\WithdrawRequest as Model;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchWithdrawRequestForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchTransactionMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $acceptedParams = ['from_date', 'to_date', 'status', 'buyer', 'base_currency'];

        if (Emoney::isUsingNewAlias()) {
            $acceptedParams = array_merge($acceptedParams, ['source', 'type']);
        }

        $this->asGet()
            ->action('emoney/transaction')
            ->acceptPageParams($acceptedParams)
            ->setValue([
                'from_date' => null,
                'to_date'   => null,
            ]);
    }

    private function initializeForOld(): void
    {
        $this->addBasic(['component' => 'SFScrollView'])
            ->showWhen(['falsy', 'filters'])
            ->addFields(
                Builder::text('buyer')
                    ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                    ->placeholder(__p('ewallet::phrase.buyer'))
                    ->label(__p('ewallet::phrase.buyer'))
                    ->className('mb2')
                    ->delayTime(200)
                    ->forBottomSheetForm('SFSearchBox'),
                Builder::button('filters')
                    ->forBottomSheetForm(),
                Builder::choice('base_currency')
                    ->label(__p('ewallet::phrase.base_currency'))
                    ->options(Emoney::getBaseCurrencyOptions())
                    ->forBottomSheetForm()
                    ->autoSubmit(),
                Builder::choice('status')
                    ->label(__p('core::phrase.status'))
                    ->options(Emoney::getTransactionStatusOptions())
                    ->forBottomSheetForm()
                    ->autoSubmit(),
                Builder::date('from_date')
                    ->label(__p('core::web.from'))
                    ->startOfDay()
                    ->forBottomSheetForm()
                    ->autoSubmit()
                    ->maxDate(Carbon::now()->toISOString())
                    ->yup(
                        Yup::date()->nullable()
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                    ),
                Builder::date('to_date')
                    ->label(__p('core::phrase.to_label'))
                    ->endOfDay()
                    ->forBottomSheetForm()
                    ->autoSubmit()
                    ->maxDate(Carbon::now()->toISOString())
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->min(['ref' => 'from_date'])
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                            ->setError('min', __p('ewallet::validation.the_end_time_should_be_greater_than_the_start_time'))
                            ->setError(
                                'minDate',
                                __p('ewallet::validation.the_end_time_should_be_greater_than_the_current_time')
                            )
                    ),
            );

        $bottomSheet = $this->addSection(['name' => 'bottomSheet']);

        $bottomSheet->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['buyer', 'base_currency', 'status', 'from_date', 'to_date'])
                ->showWhen(['truthy', 'filters']),
            Builder::choice('base_currency')
                ->label(__p('ewallet::phrase.base_currency'))
                ->options(Emoney::getBaseCurrencyOptions())
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->options(Emoney::getTransactionStatusOptions())
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            Builder::date('from_date')
                ->label(__p('core::web.from'))
                ->startOfDay()
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->maxDate(Carbon::now()->toISOString())
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::date('to_date')
                ->label(__p('core::phrase.to_label'))
                ->endOfDay()
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard')
                ->maxDate(Carbon::now()->toISOString())
                ->showWhen(['truthy', 'filters'])
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                        ->setError('min', __p('ewallet::validation.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError(
                            'minDate',
                            __p('ewallet::validation.the_end_time_should_be_greater_than_the_current_time')
                        )
                ),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }

    protected function initialize(): void
    {
        if (!Emoney::isUsingNewAlias()) {
            $this->initializeForOld();
            return;
        }

        $basic = $this->addBasic(['component' => 'SFScrollView'])
            ->showWhen(['falsy', 'filters']);

        $basic->addFields(
            Builder::text('buyer')
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                ->placeholder(__p('ewallet::web.ewallet_user'))
                ->label(__p('ewallet::web.ewallet_user'))
                ->className('mb2')
                ->delayTime(200)
                ->forBottomSheetForm('SFSearchBox'),
            Builder::button('filters')
                ->forBottomSheetForm(),
        );

        $this->getBasicFields($basic);

        $bottomSheet = $this->addSection(['name' => 'bottomSheet']);

        $this->getBottomSheetFields($bottomSheet);
    }

    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            $this->getClearSearchFieldsFlatten()
                ->targets(['buyer', 'base_currency', 'status', 'from_date', 'to_date', 'source', 'type']),
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::choice('base_currency')
                ->label(__p('ewallet::phrase.base_currency'))
                ->options(Emoney::getBaseCurrencyOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::choice('source')
                ->label(__p('ewallet::web.source'))
                ->options(Emoney::getSourceOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::choice('type')
                ->label(__p('ewallet::web.action'))
                ->options(Emoney::getTypeOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->options(Emoney::getTransactionStatusOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::date('from_date')
                ->label(__p('core::web.from'))
                ->startOfDay()
                ->forBottomSheetForm()
                ->autoSubmit()
                ->maxDate(Carbon::now()->toISOString())
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::date('to_date')
                ->label(__p('core::phrase.to_label'))
                ->endOfDay()
                ->forBottomSheetForm()
                ->autoSubmit()
                ->maxDate(Carbon::now()->toISOString())
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                        ->setError('min', __p('ewallet::validation.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError(
                            'minDate',
                            __p('ewallet::validation.the_end_time_should_be_greater_than_the_current_time')
                        )
                ),
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['buyer', 'base_currency', 'status', 'from_date', 'to_date', 'source', 'type'])
                ->showWhen(['truthy', 'filters']),
            Builder::choice('base_currency')
                ->label(__p('ewallet::phrase.base_currency'))
                ->options(Emoney::getBaseCurrencyOptions())
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            Builder::choice('source')
                ->label(__p('ewallet::web.source'))
                ->options(Emoney::getSourceOptions())
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            Builder::choice('type')
                ->label(__p('ewallet::web.action'))
                ->options(Emoney::getTypeOptions())
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->options(Emoney::getTransactionStatusOptions())
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            Builder::date('from_date')
                ->label(__p('core::web.from'))
                ->startOfDay()
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard')
                ->showWhen(['truthy', 'filters'])
                ->maxDate(Carbon::now()->toISOString())
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::date('to_date')
                ->label(__p('core::phrase.to_label'))
                ->endOfDay()
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard')
                ->maxDate(Carbon::now()->toISOString())
                ->showWhen(['truthy', 'filters'])
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                        ->setError('min', __p('ewallet::validation.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError(
                            'minDate',
                            __p('ewallet::validation.the_end_time_should_be_greater_than_the_current_time')
                        )
                ),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }
}
