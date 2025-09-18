<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\DurationScope;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @driverName live_video.search
 * @driverType form
 * @preload    1
 */
class SearchLiveVideoMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/live-video/search')
            ->acceptPageParams(['q', 'sort', 'when', 'returnUrl', 'streaming', 'duration', 'is_featured'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);
        $basic->addFields(
            $this->getSearchFields()
                ->placeholder(__p('livestreaming::phrase.search_live_videos')),
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
                ->targets(['sort', 'when', 'is_featured', 'duration', 'streaming']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('livestreaming::phrase.search_live_videos'))
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
            Builder::choice('duration')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('livestreaming::phrase.duration'))
                ->options([['label' => __p('livestreaming::phrase.longer_than_minutes', ['value' => DurationScope::getSetting()]), 'value' => DurationScope::DURATION_LONGER], ['label' => __p('livestreaming::phrase.shorter_than_minutes', ['value' => DurationScope::getSetting()]), 'value' => DurationScope::DURATION_SHORTER]]),
            Builder::switch('streaming')
                ->forBottomSheetForm()
                ->margin('none')
                ->label(__p('livestreaming::phrase.live')),
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
                ->targets(['sort', 'when', 'is_featured', 'duration', 'streaming'])
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
            Builder::choice('duration')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('livestreaming::phrase.duration'))
                ->variant('standard-inlined')
                ->options([['label' => __p('livestreaming::phrase.longer_than_minutes', ['value' => DurationScope::getSetting()]), 'value' => DurationScope::DURATION_LONGER], ['label' => __p('livestreaming::phrase.shorter_than_minutes', ['value' => DurationScope::getSetting()]), 'value' => DurationScope::DURATION_SHORTER]])
                ->showWhen(['truthy', 'filters']),
            Builder::switch('streaming')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('livestreaming::phrase.live'))
                ->showWhen(['truthy', 'filters']),
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
    protected function getSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_LATEST],
            ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED],
            ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED],
        ];
    }

    /**
     * @return array<int, mixed>
     */
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
