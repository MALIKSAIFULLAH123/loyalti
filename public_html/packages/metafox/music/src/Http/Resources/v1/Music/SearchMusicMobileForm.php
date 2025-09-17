<?php

namespace MetaFox\Music\Http\Resources\v1\Music;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Music\Support\Facades\Music;
use MetaFox\Platform\Support\Browse\Browse;

class SearchMusicMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/music/search')
            ->acceptPageParams(['q', 'sort', 'when', 'genre_id', 'returnUrl'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);
        $basic->addFields(
            $this->getSearchFields()
                ->placeholder($this->getSearchFieldPlaceholder()),
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
                ->targets(['sort', 'when', 'category_id', 'genre_id', 'is_featured']),
            $this->getSearchFieldsFlatten()
                ->placeholder($this->getSearchFieldPlaceholder())
        );

        $this->getBasicFields($basic);
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->showWhen(['truthy', 'filters'])
                ->targets(['sort', 'when', 'category_id', 'genre_id', 'is_featured']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters'])
                ->options($this->getSortOptions())
                ->variant('standard-inlined'),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters'])
                ->options($this->getWhenOptions())
                ->variant('standard-inlined'),
            $this->getBottomSheetFieldFeatured(),
            $this->getBottomSheetFieldGenres(),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.filter')),
        );
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->forBottomSheetForm()
                ->autoSubmit()
                ->options($this->getSortOptions()),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->forBottomSheetForm()
                ->autoSubmit()
                ->options($this->getWhenOptions()),
            $this->getBasicFieldFeatured(),
            $this->getBasicFieldGenres()
        );
    }

    protected function getBasicFieldFeatured(): ?\MetaFox\Form\Mobile\SwitchField
    {
        return Builder::switch('is_featured')
            ->forBottomSheetForm()
            ->margin('none')
            ->label(__p('core::phrase.featured'));
    }

    protected function getBottomSheetFieldFeatured(): ?\MetaFox\Form\Mobile\SwitchField
    {
        return Builder::switch('is_featured')
            ->forBottomSheetForm()
            ->variant('standard-inlined')
            ->label(__p('core::phrase.featured'))
            ->showWhen(['truthy', 'filters']);
    }

    protected function getBasicFieldGenres(): ?\MetaFox\Form\Mobile\Autocomplete
    {
        return Builder::autocomplete('genre_id')
            ->label(__p('music::phrase.genres'))
            ->forBottomSheetForm()
            ->useOptionContext()
            ->searchEndpoint('/music-genre')
            ->searchParams(['level' => 0]);
    }

    protected function getBottomSheetFieldGenres(): ?\MetaFox\Form\Mobile\Autocomplete
    {
        return Builder::autocomplete('genre_id')
            ->label(__p('music::phrase.genres'))
            ->forBottomSheetForm()
            ->useOptionContext()
            ->showWhen(['truthy', 'filters'])
            ->searchEndpoint('/music-genre')
            ->variant('standard-inlined')
            ->searchParams(['level' => 0]);
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

    protected function getWhenOptions(): array
    {
        return [
            ['label' => __p('core::phrase.when.all'), 'value' => Browse::WHEN_ALL],
            ['label' => __p('core::phrase.when.this_month'), 'value' => Browse::WHEN_THIS_MONTH],
            ['label' => __p('core::phrase.when.this_week'), 'value' => Browse::WHEN_THIS_WEEK],
            ['label' => __p('core::phrase.when.today'), 'value' => Browse::WHEN_TODAY],
        ];
    }

    protected function getEntityTypes(): array
    {
        return Music::getEntityTypeOptions();
    }

    protected function getSearchFieldPlaceholder(): string
    {
        return __p('music::phrase.search_items');
    }
}
