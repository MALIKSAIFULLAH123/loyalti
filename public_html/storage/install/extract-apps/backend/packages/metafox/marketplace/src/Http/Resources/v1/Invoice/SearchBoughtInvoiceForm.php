<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\SearchFormRequest;
use MetaFox\Marketplace\Models\Invoice as Model;
use MetaFox\Marketplace\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchBoughtInvoiceForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName marketplace_invoice.bought_search
 * @driverType form
 * @preload    1
 */
class SearchBoughtInvoiceForm extends AbstractForm
{
    protected ?int $defaultListingId = null;

    protected string $view = ViewScope::VIEW_BOUGHT;

    public function boot(SearchFormRequest $request): void
    {
        $params                 = $request->all();
        $this->defaultListingId = Arr::get($params, 'listing_id');
    }

    protected function prepare(): void
    {
        $this->action(apiUrl('marketplace-invoice.index'))
            ->acceptPageParams(['view', 'listing_id', 'from', 'to', 'status'])
            ->setValue([
                'listing_id' => $this->defaultListingId,
                'status'     => ListingFacade::getAllPaymentStatus(),
                'from'       => null,
                'to'         => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::autocomplete('listing_id')
                ->setAttribute('option_default', $this->getListingOption())
                ->searchEndpoint('marketplace/search-suggestion')
                ->searchParams(
                    [
                        'view'       => $this->view,
                        'listing_id' => ':listing_id',
                    ]
                )
                ->label(__p('marketplace::phrase.listing'))
                ->marginDense()
                ->sizeSmall()
                ->sxFieldWrapper($this->getResponsiveSx()),
            Builder::date('from')
                ->forAdminSearchForm()
                ->label(__p('marketplace::phrase.from'))
                ->maxDate(Carbon::now()->toISOString())
                ->sxFieldWrapper($this->getResponsiveSx())
                ->startOfDay()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('marketplace::phrase.from')]))
                ),
            Builder::date('to')
                ->forAdminSearchForm()
                ->label(__p('marketplace::phrase.to'))
                ->maxDate(Carbon::now()->toISOString())
                ->sxFieldWrapper($this->getResponsiveSx())
                ->endOfDay()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from'])
                        ->setError('min', __p('marketplace::phrase.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('marketplace::phrase.to')]))
                ),
            Builder::choice('status')
                ->options($this->getPaymentStatus())
                ->forAdminSearchForm()
                ->disableClearable()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->label(__p('marketplace::phrase.status')),
            Builder::submit()
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->forAdminSearchForm()
                ->excludeFields(['view'])
                ->sizeMedium(),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getPaymentStatus(): array
    {
        return ListingFacade::getInvoiceStatusOptionForFrom();
    }

    protected function getListingOption(): array
    {
        if (null == $this->defaultListingId) {
            return [];
        }

        return ListingFacade::getListingForForm(user(), [
            'view'       => $this->view,
            'listing_id' => $this->defaultListingId,
        ]);
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
