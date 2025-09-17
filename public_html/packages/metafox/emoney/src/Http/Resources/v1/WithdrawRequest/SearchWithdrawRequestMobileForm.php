<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest;

use Carbon\Carbon;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\WithdrawRequest as Model;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
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
class SearchWithdrawRequestMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asGet()
            ->action('emoney/request')
            ->acceptPageParams(['from_date', 'to_date', 'status'])
            ->setValue([
                'from_date' => null,
                'to_date'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])
            ->showWhen(['falsy', 'filters']);
        $basic->addFields(
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
                ->targets(['status', 'from_date', 'to_date']),
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->options(Emoney::getRequestStatusOptions())
                ->forBottomSheetForm()
                ->autoSubmit(),
            Builder::date('from_date')
                ->label(__p('core::web.from'))
                ->forBottomSheetForm()
                ->autoSubmit()
                ->maxDate(Carbon::now()->toISOString())
                ->startOfDay()
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
                ->targets(['status', 'from_date', 'to_date'])
                ->showWhen(['truthy', 'filters']),
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->options(Emoney::getRequestStatusOptions())
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            Builder::date('from_date')
                ->label(__p('core::web.from'))
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard')
                ->startOfDay()
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
                ->showWhen(['truthy', 'filters'])
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
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }
}
