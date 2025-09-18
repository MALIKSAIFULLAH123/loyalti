<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use MetaFox\Core\Support\Facades\Country;
use MetaFox\Form\Builder;
use MetaFox\Form\Html\BuiltinSearchForm;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\SortScope;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchListingForm.
 *
 * @property Model $resource
 */
class SearchListingForm extends BuiltinSearchForm
{
    protected function prepare(): void
    {
        $this->action('/marketplace/search')
            ->asGet()
            ->acceptPageParams(['q', 'sort', 'when', 'category_id', 'country_iso', 'price_from', 'price_to', 'is_featured'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('marketplace::phrase.search_listings'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['category_id', 'q', 'view']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->marginNormal()
                ->options($this->getSortOptions()),
            Builder::choice('country_iso')
                ->label(__p('core::country.country'))
                ->marginNormal()
                ->options(Country::buildCountrySearchForm()),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->options($this->getWhenOptions()),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
            Builder::divider('divider_1')
                ->sx([
                    'mt' => 1,
                    'mb' => 1,
                ]),
            Builder::filterPrice()
                ->label(__p('core::web.price')),
            Builder::filterCategory('category_id')
                ->label(__p('core::phrase.categories'))
                ->apiUrl('/marketplace-category')
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
