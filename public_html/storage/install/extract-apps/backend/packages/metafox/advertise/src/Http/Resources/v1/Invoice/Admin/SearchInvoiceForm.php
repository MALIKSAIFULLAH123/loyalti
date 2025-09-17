<?php

namespace MetaFox\Advertise\Http\Resources\v1\Invoice\Admin;

use Illuminate\Support\Arr;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Advertise\Models\Invoice as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchInvoiceForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInvoiceForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/advertise/invoice')
            ->asGet()
            ->submitAction('@formAdmin/search/SUBMIT')
            ->setValue([
                'start_date'     => null,
                'end_date'       => null,
                'payment_status' => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::date('start_date')
                ->label(__p('core::web.from'))
                ->startOfDay()
                ->forAdminSearchForm()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::date('end_date')
                ->label(__p('advertise::phrase.to_ucfirst'))
                ->endOfDay()
                ->forAdminSearchForm()->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'start_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('advertise::phrase.to_ucfirst')]))
                        ->setError('min', __p('advertise::phrase.the_end_time_should_be_greater_than_the_start_time'))
                ),
            Builder::choice('payment_status')
                ->label(__p('core::web.status'))
                ->options($this->getStatusOptions())
                ->forAdminSearchForm(),
            Builder::submit()
                ->label(__p('core::phrase.search'))
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset')),
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
}
