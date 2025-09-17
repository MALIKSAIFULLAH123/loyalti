<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\SearchFormRequest;
use MetaFox\Marketplace\Models\Invoice as Model;
use MetaFox\Marketplace\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchBoughtInvoiceMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName marketplace_invoice.bought_search
 * @driverType form
 */
class SearchBoughtInvoiceMobileForm extends AbstractForm
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
        $value = [];

        if ($this->defaultListingId) {
            Arr::set($value, 'listing_id', $this->defaultListingId);
        }

        $this->title(__p('marketplace::phrase.search_invoices'))
            ->action(apiUrl('marketplace-invoice.index'))
            ->acceptPageParams(['view', 'listing_id', 'from', 'to', 'status'])
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);

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
                ->targets(['from', 'to', 'listing_id', 'status']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('marketplace::phrase.search_invoices'))
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::autocomplete('listing_id')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->setAttribute('option_default', $this->getListingOption())
                ->useOptionContext()
                ->searchEndpoint('marketplace/search-suggestion')
                ->searchParams(['view' => $this->view])
                ->labelKey('label')
                ->valueKey('value')
                ->label(__p('marketplace::phrase.listing')),
            Builder::date('from')
                ->autoSubmit()
                ->forBottomSheetForm()
                ->label(__p('marketplace::phrase.from'))
                ->maxDate(Carbon::now()->toISOString())
                ->setAttribute('datePickerMode', 'date')
                ->setAttribute('startOfDay', true),
            Builder::date('to')
                ->autoSubmit()
                ->forBottomSheetForm()
                ->label(__p('marketplace::phrase.to'))
                ->maxDate(Carbon::now()->toISOString())
                ->setAttribute('datePickerMode', 'date')
                ->setAttribute('endOfDay', true),
            Builder::choice('status')
                ->autoSubmit()
                ->options($this->getPaymentStatus())
                ->forBottomSheetForm()
                ->disableClearable()
                ->label(__p('marketplace::phrase.status')),
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['from', 'to', 'listing_id', 'status'])
                ->showWhen(['truthy', 'filters']),
            Builder::autocomplete('listing_id')
                ->forBottomSheetForm()
                ->setAttribute('option_default', $this->getListingOption())
                ->useOptionContext()
                ->searchEndpoint('marketplace/search-suggestion')
                ->searchParams(['view' => $this->view])
                ->variant('standard-inlined')
                ->labelKey('label')
                ->valueKey('value')
                ->showWhen(['truthy', 'filters'])
                ->label(__p('marketplace::phrase.listing')),
            Builder::date('from')
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters'])
                ->variant('standard')
                ->label(__p('marketplace::phrase.from'))
                ->maxDate(Carbon::now()->toISOString()),
            Builder::date('to')
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters'])
                ->variant('standard')
                ->label(__p('marketplace::phrase.to'))
                ->maxDate(Carbon::now()->toISOString()),
            Builder::choice('status')
                ->options($this->getPaymentStatus())
                ->forBottomSheetForm()
                ->disableClearable()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters'])
                ->label(__p('marketplace::phrase.status')),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
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
}
