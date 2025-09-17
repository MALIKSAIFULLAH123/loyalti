<?php

namespace MetaFox\Event\Http\Resources\v1\Event\SearchFrom;

use Illuminate\Support\Str;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Support\Browse\Scopes\Event\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Event\WhenScope;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SearchMobileForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
abstract class SearchMobileForm extends AbstractForm
{
    protected const ACCEPT_PAGE_PARAMS_DEFAULT = ['q', 'view', 'when', 'sort', 'where', 'category_id', 'is_online', 'is_featured', 'returnUrl'];
    protected const ACCEPT_FIELD_DEFAULT       = ['sort', 'when', 'where', 'is_online', 'is_featured', 'category_id'];
    protected const NAME_METHOD_BASIC_FIELD    = 'get%sBasicField';
    protected const NAME_METHOD_BOTTOM_FIELD   = 'get%sBottomField';

    protected function prepare(): void
    {
        $this->action('/event/search')
            ->asGet()
            ->acceptPageParams(self::ACCEPT_PAGE_PARAMS_DEFAULT)
            ->setValue($this->getValues());
    }

    protected function initialize(): void
    {
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
        foreach ($this->getAcceptFieldBasic() as $item) {
            $methodName = sprintf(self::NAME_METHOD_BASIC_FIELD, Str::studly($item));
            $section->addFields($this->$methodName());
        }
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['sort', 'when', 'where', 'is_online', 'category_id', 'is_featured'])
                ->showWhen(['truthy', 'filters']),
        );

        foreach ($this->getAcceptFieldBottom() as $item) {
            $methodName = sprintf(self::NAME_METHOD_BOTTOM_FIELD, Str::studly($item));
            $section->addFields($this->$methodName());
        }

        $section->addFields(
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }

    protected function getAcceptFieldBasic(): array
    {
        return self::ACCEPT_FIELD_DEFAULT;
    }

    protected function getAcceptFieldBottom(): array
    {
        return self::ACCEPT_FIELD_DEFAULT;
    }

    protected function getValues(): array
    {
        return [];
    }

    protected function getWhenBasicField(): AbstractField
    {
        return Builder::choice('when')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->label(__p('core::phrase.when_label'))
            ->options($this->getWhenOptions());
    }

    protected function getSortBasicField(): AbstractField
    {
        return Builder::choice('sort')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->label(__p('core::phrase.sort_label'))
            ->options($this->getSortOptions());
    }

    protected function getWhereBasicField(): AbstractField
    {
        return Builder::choice('where')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->enableSearch()
            ->label(__p('localize::country.country'))
            ->options(Country::buildCountrySearchForm());
    }

    protected function getIsOnlineBasicField(): AbstractField
    {
        return Builder::switch('is_online')
            ->forBottomSheetForm()
            ->margin('none')
            ->label(__p('event::phrase.online'));
    }

    protected function getIsFeaturedBasicField(): AbstractField
    {
        return Builder::switch('is_featured')
            ->forBottomSheetForm()
            ->margin('none')
            ->label(__p('core::phrase.featured'));
    }

    protected function getCategoryIdBasicField(): AbstractField
    {
        return Builder::autocomplete('category_id')
            ->forBottomSheetForm()
            ->useOptionContext()
            ->label(__p('core::phrase.categories'))
            ->searchEndpoint('/event-category')
            ->searchParams(['level' => 0]);
    }

    protected function getWhenBottomField(): AbstractField
    {
        return Builder::choice('when')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->label(__p('core::phrase.when_label'))
            ->variant('standard-inlined')
            ->options($this->getWhenOptions())
            ->showWhen(['truthy', 'filters']);
    }

    protected function getSortBottomField(): AbstractField
    {
        return Builder::choice('sort')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->label(__p('core::phrase.sort_label'))
            ->variant('standard-inlined')
            ->options($this->getSortOptions())
            ->showWhen(['truthy', 'filters']);
    }

    protected function getWhereBottomField(): AbstractField
    {
        return Builder::choice('where')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->label(__p('localize::country.country'))
            ->marginNormal()
            ->enableSearch()
            ->variant('standard-inlined')
            ->options(Country::buildCountrySearchForm())
            ->showWhen(['truthy', 'filters']);
    }

    protected function getIsOnlineBottomField(): AbstractField
    {
        return Builder::switch('is_online')
            ->forBottomSheetForm()
            ->variant('standard-inlined')
            ->label(__p('event::phrase.online'))
            ->showWhen(['truthy', 'filters']);
    }

    protected function getIsFeaturedBottomField(): AbstractField
    {
        return Builder::switch('is_featured')
            ->forBottomSheetForm()
            ->variant('standard-inlined')
            ->label(__p('core::phrase.featured'))
            ->showWhen(['truthy', 'filters']);
    }

    protected function getCategoryIdBottomField(): AbstractField
    {
        return Builder::autocomplete('category_id')
            ->forBottomSheetForm()
            ->useOptionContext()
            ->label(__p('core::phrase.categories'))
            ->searchEndpoint('/event-category')
            ->searchParams(['level' => 0])
            ->variant('standard-inlined')
            ->showWhen(['truthy', 'filters']);
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
