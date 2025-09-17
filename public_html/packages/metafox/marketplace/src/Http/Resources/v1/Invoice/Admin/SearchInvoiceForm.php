<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice\Admin;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Marketplace\Models\Invoice as Model;
use MetaFox\Marketplace\Support\Facade\Listing;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchInvoiceForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInvoiceForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/marketplace/invoice')
            ->asGet()
            ->submitAction('@formAdmin/search/SUBMIT')
            ->setValue([
                'from'   => null,
                'to'     => null,
                'status' => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::date('from')
                ->label(__p('core::web.from'))
                ->startOfDay()
                ->forAdminSearchForm()
                ->maxDate(Carbon::now()->toISOString())
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::date('to')
                ->label(__p('marketplace::phrase.to'))
                ->endOfDay()
                ->maxDate(Carbon::now()->toISOString())
                ->forAdminSearchForm()->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('marketplace::phrase.to')]))
                        ->setError('min', __p('marketplace::phrase.the_end_time_should_be_greater_than_the_start_time'))
                ),
            Builder::choice('status')
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
        $options = Listing::getInvoiceStatusOptionForFrom();

        if (!count($options)) {
            return [];
        }

        Arr::prepend($options, [
            'label' => __p('marketplace::phrase.all_status'),
            'value' => null,
        ]);

        return $options;
    }
}
