<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\Event;

use MetaFox\Event\Support\Browse\Scopes\Event\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Event\ViewScope;
use MetaFox\Event\Support\Browse\Scopes\Event\WhenScope;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @preload 1
 */
class SearchEventMapForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/event/search-map')
            ->acceptPageParams([
                'q', 'when', 'sort', 'limit', 'returnUrl', 'bounds_west',
                'bounds_east', 'bounds_south', 'bounds_north', 'zoom', 'view',
            ])
            ->setValue([
                'view'  => ViewScope::VIEW_ON_MAP,
                'limit' => MetaFoxConstant::VIEW_5_NEAREST,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('event::phrase.search_events'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['category_id', 'q', 'view']),
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
                ->required()
                ->marginNormal()
                ->options($this->getLimitOptions()),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured'))
                ->marginDense()
                ->setAttribute('sxLabel', []),
        );
    }

    protected function getLimitOptions(): array
    {
        return [
            [
                'label' => __p('event::phrase.nearest.view_5_nearest_events'),
                'value' => MetaFoxConstant::VIEW_5_NEAREST,
            ], [
                'label' => __p('event::phrase.nearest.view_10_nearest_events'),
                'value' => MetaFoxConstant::VIEW_10_NEAREST,
            ], [
                'label' => __p('event::phrase.nearest.view_15_nearest_events'),
                'value' => MetaFoxConstant::VIEW_15_NEAREST,
            ], [
                'label' => __p('event::phrase.nearest.view_20_nearest_events'),
                'value' => MetaFoxConstant::VIEW_20_NEAREST,
            ],
        ];
    }

    protected function getSortOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.sort.recent'),
                'value' => Browse::SORT_RECENT,
            ], [
                'label' => __p('core::phrase.sort.most_liked'),
                'value' => Browse::SORT_MOST_LIKED,
            ], [
                'label' => __p('core::phrase.sort.most_discussed'),
                'value' => Browse::SORT_MOST_DISCUSSED,
            ], [
                'label' => __p('event::phrase.sort.most_interested'),
                'value' => SortScope::SORT_MOST_INTERESTED,
            ], [
                'label' => __p('event::phrase.sort.most_going'),
                'value' => SortScope::SORT_MOST_MEMBER,
            ],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function getWhenOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.when.all'),
                'value' => Browse::WHEN_ALL,
            ], [
                'label' => __p('core::phrase.when.this_month'),
                'value' => Browse::WHEN_THIS_MONTH,
            ], [
                'label' => __p('core::phrase.when.this_week'),
                'value' => Browse::WHEN_THIS_WEEK,
            ], [
                'label' => __p('core::phrase.when.today'),
                'value' => Browse::WHEN_TODAY,
            ], [
                'label' => __p('event::phrase.when.upcoming'),
                'value' => WhenScope::WHEN_UPCOMING,
            ], [
                'label' => __p('event::phrase.when.ongoing'),
                'value' => WhenScope::WHEN_ONGOING,
            ],
        ];
    }
}
