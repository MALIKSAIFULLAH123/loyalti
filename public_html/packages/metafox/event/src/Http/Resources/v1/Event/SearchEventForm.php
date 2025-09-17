<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\Event;

use MetaFox\Core\Support\Facades\Country;
use MetaFox\Event\Support\Browse\Scopes\Event\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Event\WhenScope;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @preload 1
 */
class SearchEventForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/event/search')
            ->acceptPageParams(['q', 'country', 'when', 'sort', 'where', 'category_id', 'is_online', 'is_featured', 'returnUrl'])
            ->setValue([
                'when' => Browse::WHEN_ALL,
                'view' => Browse::VIEW_SEARCH,
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
            Builder::choice('where')
                ->label(__p('localize::country.country'))
                ->marginNormal()
                ->options(Country::buildCountrySearchForm()),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->marginNormal()
                ->options([
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
                ]),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->options([
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
                ]),
            Builder::switch('is_online')
                ->label(__p('event::phrase.online'))
                ->marginDense()
                ->setAttribute('sxLabel', []),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured'))
                ->marginDense()
                ->setAttribute('sxLabel', []),
            Builder::filterCategory('category_id')
                ->label(__p('core::phrase.categories'))
                ->apiUrl('/event-category'),
        );
    }
}
