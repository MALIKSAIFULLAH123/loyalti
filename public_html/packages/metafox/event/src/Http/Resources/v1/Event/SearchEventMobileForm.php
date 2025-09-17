<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\Event;

use MetaFox\Core\Support\Facades\Country;
use MetaFox\Event\Support\Browse\Scopes\Event\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Event\WhenScope;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @preload 1
 */
class SearchEventMobileForm extends AbstractForm
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
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);

        $basic->addFields(
            $this->getSearchFields()
                ->placeholder(__p('event::phrase.search_events')),
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
                ->targets(['sort', 'when', 'where', 'is_online', 'category_id', 'is_featured']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('event::phrase.search_events')),
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
            Builder::choice('when')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::phrase.when_label'))
                ->options($this->getWhenOptions()),
            Builder::choice('where')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->enableSearch()
                ->label(__p('localize::country.country'))
                ->options(Country::buildCountrySearchForm()),
            Builder::switch('is_online')
                ->forBottomSheetForm()
                ->margin('none')
                ->label(__p('event::phrase.online')),
            Builder::switch('is_featured')
                ->forBottomSheetForm()
                ->margin('none')
                ->label(__p('core::phrase.featured')),
            Builder::autocomplete('category_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->label(__p('core::phrase.categories'))
                ->searchEndpoint('/event-category')
                ->searchParams(['level' => 0]),
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['sort', 'when', 'where', 'is_online', 'category_id', 'is_featured'])
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
            Builder::choice('where')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('localize::country.country'))
                ->marginNormal()
                ->enableSearch()
                ->variant('standard-inlined')
                ->options(Country::buildCountrySearchForm())
                ->showWhen(['truthy', 'filters']),
            Builder::switch('is_online')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('event::phrase.online'))
                ->showWhen(['truthy', 'filters']),
            Builder::switch('is_featured')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.featured'))
                ->showWhen(['truthy', 'filters']),
            Builder::autocomplete('category_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->label(__p('core::phrase.categories'))
                ->searchEndpoint('/event-category')
                ->searchParams(['level' => 0])
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }

    /**
     * @return array<int, mixed>
     */
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
