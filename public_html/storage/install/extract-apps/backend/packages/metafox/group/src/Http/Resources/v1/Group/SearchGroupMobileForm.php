<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Group;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @preload 1
 */
class SearchGroupMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/group/search')
            ->acceptPageParams(['q', 'when', 'sort', 'category_id', 'is_featured', 'returnUrl'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])
            ->showWhen(['falsy', 'filters']);

        $basic->addFields(
            $this->getSearchFields()
                ->placeholder(__p('group::phrase.search_groups')),
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
                ->targets(['sort', 'when', 'category_id', 'is_featured']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('group::phrase.search_groups'))
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
            Builder::switch('is_featured')
                ->forBottomSheetForm()
                ->margin('none')
                ->label(__p('core::phrase.featured')),
            Builder::autocomplete('category_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->label(__p('core::phrase.categories'))
                ->searchEndpoint('/group/category')
                ->searchParams(['level' => 0]),
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $rules = ['truthy', 'filters'];
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->showWhen(['truthy', 'filters'])
                ->targets(['sort', 'when', 'category_id', 'is_featured']),
            Builder::choice('sort')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.sort_label'))
                ->showWhen($rules)
                ->options($this->getSortOptions()),
            Builder::choice('when')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.when_label'))
                ->showWhen($rules)
                ->options($this->getWhenOptions()),
            Builder::switch('is_featured')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.featured'))
                ->showWhen(['truthy', 'filters']),
            Builder::autocomplete('category_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.categories'))
                ->showWhen($rules)
                ->searchEndpoint('/group/category')
                ->searchParams(['level' => 0]),
            Builder::submit()
                ->showWhen($rules)
                ->label(__p('core::phrase.show_results')),
        );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.latest'), 'value' => 'latest'],
            ['label' => __p('core::phrase.sort.most_joined'), 'value' => 'most_member'],
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
                'value' => 'all',
            ], [
                'label' => __p('core::phrase.when.this_month'),
                'value' => 'this_month',
            ], [
                'label' => __p('core::phrase.when.this_week'),
                'value' => 'this_week',
            ], [
                'label' => __p('core::phrase.when.today'),
                'value' => 'today',
            ],
        ];
    }
}
