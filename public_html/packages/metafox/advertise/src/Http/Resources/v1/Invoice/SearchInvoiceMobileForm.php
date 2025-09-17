<?php

namespace MetaFox\Advertise\Http\Resources\v1\Invoice;

use Illuminate\Support\Arr;
use MetaFox\Advertise\Models\Invoice as Model;
use MetaFox\Advertise\Support\Facades\Support;
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
 * Class SearchInvoiceMobileForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInvoiceMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('advertise::phrase.search_invoice'))
            ->action('advertise/invoice')
            ->acceptPageParams(['start_date', 'end_date', 'status'])
            ->asGet()
            ->setValue([
                'start_date' => null,
                'end_date'   => null,
                'status'     => null,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::dateTime('start_date')
                    ->label(__p('core::web.from'))
                    ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                    ->datePickerMode('date')
                    ->setAttribute('startOfDay', true)
                    ->yup(
                        Yup::date()->nullable()
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                    ),
                Builder::dateTime('end_date')
                    ->label(__p('advertise::phrase.to_ucfirst'))
                    ->displayFormat(MetaFoxConstant::DISPLAY_FORMAT_TIME)
                    ->datePickerMode('date')
                    ->setAttribute('endOfDay', true)
                    ->yup(Yup::date()
                        ->nullable()
                        ->min(['ref' => 'start_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('advertise::phrase.to_ucfirst')]))
                        ->setError('min', __p('advertise::phrase.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError(
                            'minDate',
                            __p('advertise::phrase.the_end_time_should_be_greater_than_the_current_time')
                        )),
                Builder::choice('status')
                    ->label(__p('core::web.status'))
                    ->enableSearch(false)
                    ->options($this->getStatusOptions()),
            );
    }

    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            $this->getClearSearchFieldsFlatten()
                ->targets(['status', 'start_date', 'end_date']),
        );

        $this->getBasicFields($basic);
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['status', 'start_date', 'end_date'])
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters']),
            Builder::date('start_date')
                ->label(__p('core::web.from'))
                ->placeholder(__p('core::web.from'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->variant('standard')
                ->forBottomSheetForm()
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                )
                ->showWhen(['truthy', 'filters']),
            Builder::date('end_date')
                ->label(__p('advertise::phrase.to_ucfirst'))
                ->placeholder(__p('advertise::phrase.to_ucfirst'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->variant('standard')
                ->forBottomSheetForm()
                ->yup(Yup::date()
                    ->nullable()
                    ->min(['ref' => 'start_date'])
                    ->setError('typeError', __p('validation.date', ['attribute' => __p('advertise::phrase.to_ucfirst')]))
                    ->setError('min', __p('advertise::phrase.the_end_time_should_be_greater_than_the_start_time'))
                    ->setError(
                        'minDate',
                        __p('advertise::phrase.the_end_time_should_be_greater_than_the_current_time')
                    ))
                ->showWhen(['truthy', 'filters']),
            Builder::choice('status')
                ->label(__p('core::web.status'))
                ->placeholder(__p('core::web.status'))
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->autoSubmit()
                ->showWhen(['truthy', 'filters'])
                ->options($this->getStatusOptions()),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::date('start_date')
                ->label(__p('core::web.from'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->forBottomSheetForm()
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::date('end_date')
                ->label(__p('advertise::phrase.to_ucfirst'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->forBottomSheetForm()
                ->yup(Yup::date()
                    ->nullable()
                    ->min(['ref' => 'start_date'])
                    ->setError('typeError', __p('validation.date', ['attribute' => __p('advertise::phrase.to_ucfirst')]))
                    ->setError('min', __p('advertise::phrase.the_end_time_should_be_greater_than_the_start_time'))
                    ->setError(
                        'minDate',
                        __p('advertise::phrase.the_end_time_should_be_greater_than_the_current_time')
                    )),
            Builder::choice('status')
                ->label(__p('core::web.status'))
                ->forBottomSheetForm()
                ->enableSearch(false)
                ->autoSubmit(true)
                ->options($this->getStatusOptions()),
        );
    }

    protected function getStatusOptions(): array
    {
        $options = Support::getInvoiceStatusOptions();

        if (!count($options)) {
            return [];
        }

        Arr::prepend($options, [
            'label' => __p('advertise::phrase.all_status'),
            'value' => null,
        ]);

        return $options;
    }

    protected function getResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '50%',
                'md' => '220px',
            ],
            'width'    => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }
}
