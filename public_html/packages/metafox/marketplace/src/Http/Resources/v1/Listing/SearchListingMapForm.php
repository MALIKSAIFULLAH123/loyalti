<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @preload 1
 */
class SearchListingMapForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/marketplace/search-map')
            ->acceptPageParams([
                'q',
                'when',
                'sort',
                'limit',
                'returnUrl',
                'bounds_west',
                'bounds_east',
                'bounds_south',
                'bounds_north',
                'zoom',
                'is_featured',
                'price_from',
                'price_to',
            ])->setValue([
                'limit' => MetaFoxConstant::VIEW_5_NEAREST,
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
                ->excludeFields([
                    'category_id', 'q', 'view', 'bounds_west', 'bounds_east', 'bounds_south', 'bounds_north', 'zoom',
                ]),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->marginNormal()
                ->options($this->getSortOptions()),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->options($this->getWhenOptions()),
            Builder::choice('limit')
                ->label(__p('core::phrase.view'))
                ->marginNormal()
                ->required()
                ->options($this->getLimitOptions()),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
            Builder::divider()
                ->sx([
                    'mt' => 1,
                    'mb' => 1,
                ]),
            Builder::filterPrice()
                ->label(__p('core::web.price')),
        );
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

    protected function getLimitOptions(): array
    {
        return [
            [
                'label' => __p('marketplace::phrase.nearest.view_5_nearest_listings'),
                'value' => MetaFoxConstant::VIEW_5_NEAREST,
            ], [
                'label' => __p('marketplace::phrase.nearest.view_10_nearest_listings'),
                'value' => MetaFoxConstant::VIEW_10_NEAREST,
            ], [
                'label' => __p('marketplace::phrase.nearest.view_15_nearest_listings'),
                'value' => MetaFoxConstant::VIEW_15_NEAREST,
            ], [
                'label' => __p('marketplace::phrase.nearest.view_20_nearest_listings'),
                'value' => MetaFoxConstant::VIEW_20_NEAREST,
            ],
        ];
    }

    protected function getSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_RECENT],
            ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED],
            ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED],
        ];
    }
}
