<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm;
use MetaFox\Form\Section;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Poll\Models\Poll as Model;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @preload 1
 */
class SearchPollMobileForm extends MobileForm
{
    protected function prepare(): void
    {
        $this->action('/poll/search')
            ->acceptPageParams(['q', 'sort', 'when', 'returnUrl', 'view'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);

        $basic->addFields(
            $this->getSearchFields()
                ->placeholder(__p('poll::phrase.search_polls')),
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
                ->targets(['sort', 'when', 'is_featured']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('poll::phrase.search_polls'))
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
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->showWhen(['truthy', 'filters'])
                ->targets(['sort', 'when', 'is_featured']),
            Builder::choice('sort')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.sort_label'))
                ->showWhen(['truthy', 'filters'])
                ->options($this->getSortOptions()),
            Builder::choice('when')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.when_label'))
                ->showWhen(['truthy', 'filters'])
                ->options($this->getWhenOptions()),
            Builder::switch('is_featured')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.featured'))
                ->showWhen(['truthy', 'filters']),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function getSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.recent'), 'value' => 'latest'],
            ['label' => __p('core::phrase.sort.most_viewed'), 'value' => 'most_viewed'],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => 'most_liked'],
            ['label' => __p('core::phrase.sort.most_discussed'), 'value' => 'most_discussed'],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function getWhenOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.when.all'),
                'value' => Browse::WHEN_ALL,
            ],
            [
                'label' => __p('core::phrase.when.this_month'),
                'value' => Browse::WHEN_THIS_MONTH,
            ],
            [
                'label' => __p('core::phrase.when.this_week'),
                'value' => Browse::WHEN_THIS_WEEK,
            ],
            [
                'label' => __p('core::phrase.when.today'),
                'value' => Browse::WHEN_TODAY,
            ],
        ];
    }
}
