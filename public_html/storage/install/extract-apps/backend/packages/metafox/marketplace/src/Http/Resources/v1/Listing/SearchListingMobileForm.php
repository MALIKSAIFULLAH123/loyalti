<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use MetaFox\Core\Support\Facades\Country;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\SortScope;
use MetaFox\Platform\Support\Browse\Browse;

class SearchListingMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title('Filter')
            ->action('/marketplace/search')
            ->asGet()
            ->acceptPageParams(['q', 'sort', 'when', 'category_id', 'country_iso', 'price_from', 'price_to', 'is_featured'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);

        $basic->addFields(
            Builder::text('q')
                ->forBottomSheetForm('SFSearchBox')
                ->delayTime(200)
                ->placeholder(__p('marketplace::web.search_listings'))
                ->className('mb2'),
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
                ->targets(['sort', 'when', 'category_id', 'country_iso', 'price_from', 'price_to', 'is_featured']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('marketplace::web.search_marketplace'))
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::choice('sort')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::phrase.sort_label'))
                ->options($this->getSortOptions()),
            Builder::choice('country_iso')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->enableSearch()
                ->label(__p('core::country.country'))
                ->options(Country::buildCountrySearchForm()),
            Builder::choice('when')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::phrase.when_label'))
                ->options($this->getWhenOptions()),
            Builder::filterPrice()
                ->useOptionContext()
                ->findReplace([
                    'find'    => [','],
                    'replace' => '.',
                ])
                ->enableSearch()
                ->forBottomSheetForm(),
            Builder::autocomplete('category_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->label(__p('core::phrase.categories'))
                ->searchEndpoint('/marketplace-category')
                ->searchParams(['level' => 0]),
            Builder::switch('is_featured')
                ->forBottomSheetForm()
                ->margin('none')
                ->label(__p('core::phrase.featured')),
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['sort', 'when', 'category_id', 'country_iso', 'price_from', 'price_to', 'is_featured'])
                ->showWhen(['truthy', 'filters']),
            Builder::choice('sort')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::phrase.sort_label'))
                ->variant('standard-inlined')
                ->options($this->getSortOptions())
                ->showWhen(['truthy', 'filters']),
            Builder::choice('when')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::phrase.when_label'))
                ->variant('standard-inlined')
                ->options($this->getWhenOptions())
                ->showWhen(['truthy', 'filters']),
            Builder::choice('country_iso')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->enableSearch()
                ->label(__p('core::country.country'))
                ->variant('standard-inlined')
                ->options(Country::buildCountrySearchForm())
                ->showWhen(['truthy', 'filters']),
            Builder::filterPrice()
                ->useOptionContext()
                ->forBottomSheetForm()
                ->findReplace([
                    'find'    => [','],
                    'replace' => '.',
                ])
                ->enableSearch()
                ->showWhen(['truthy', 'filters'])
                ->variant('standard-inlined'),
            Builder::autocomplete('category_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->label(__p('core::phrase.categories'))
                ->searchEndpoint('/marketplace-category')
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters'])
                ->searchParams(['level' => 0]),
            Builder::switch('is_featured')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.featured'))
                ->showWhen(['truthy', 'filters']),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.filter')),
        );
    }

    protected function getSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_RECENT],
            ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED],
            ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED],
            ['label' => __p('marketplace::phrase.sort_lowest_price_label'), 'value' => SortScope::SORT_LOWEST_PRICE],
            ['label' => __p('marketplace::phrase.sort_highest_price_label'), 'value' => SortScope::SORT_HIGHEST_PRICE],
        ];
    }

    protected function getWhenOptions(): array
    {
        return [
            ['label' => __p('core::phrase.when.all'), 'value' => Browse::WHEN_ALL],
            ['label' => __p('core::phrase.when.this_month'), 'value' => Browse::WHEN_THIS_MONTH],
            ['label' => __p('core::phrase.when.this_week'), 'value' => Browse::WHEN_THIS_WEEK],
            ['label' => __p('core::phrase.when.today'), 'value' => Browse::WHEN_TODAY],
        ];
    }
}
