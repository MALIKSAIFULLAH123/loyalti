<?php

namespace MetaFox\Saved\Http\Resources\v1\Saved;

use Illuminate\Support\Str;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Saved\Models\Saved as Model;
use MetaFox\Saved\Support\Facade\SavedType;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchSavedMobileForm.
 * @property ?Model $resource
 */
class SearchSavedMobileForm extends MobileForm
{
    protected function prepare(): void
    {
        $method = 'getValues';
        $theme  = MetaFox::clientTheme();

        if ($theme) {
            $method = $method . Str::studly($theme);
        }

        $values = $this->$method();
        $this->action('/saved/search')
            ->setValue($values)
            ->acceptPageParams(['q', 'open', 'type', 'sort_type', 'when', 'view']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);
        $basic->addFields(
            $this->getSearchFields()
                ->placeholder(__p('saved::phrase.search_saved_items')),
            Builder::button('filters')
                ->forBottomSheetForm(),
            $this->getTypeField(),
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
                ->targets(['sort_type', 'when', 'type', 'open']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('saved::phrase.search_saved_items'))
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            $this->getOpenField(),
            $this->getSortTypeField(),
            $this->getWhenField()
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['sort_type', 'when', 'type', 'open'])
                ->showWhen(['truthy', 'filters']),
            $this->getTypeField()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            $this->getOpenField()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            $this->getSortTypeField()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            $this->getWhenField()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters']),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }

    protected function getValues(): array
    {
        return ['type' => 'all', 'when' => 'all', 'open' => 'all', 'view' => Browse::VIEW_SEARCH];
    }

    protected function getValuesFlatten(): array
    {
        return ['when' => 'all', 'open' => 'all', 'view' => Browse::VIEW_SEARCH];
    }

    protected function getWhenField(): AbstractField
    {
        return Builder::choice('when')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->label(__p('core::phrase.when_label'))
            ->options([
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
            ]);
    }

    protected function getOpenField(): AbstractField
    {
        return Builder::choice('open')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->label(__p('saved::phrase.open'))
            ->options([
                [
                    'label' => __p('core::phrase.when.all'),
                    'value' => 'all',
                ],
                [
                    'label' => __p('saved::phrase.opened'),
                    'value' => 'opened',
                ],
                [
                    'label' => __p('saved::phrase.unopened'),
                    'value' => 'unopened',
                ],
            ]);
    }

    protected function getTypeField(): AbstractField
    {
        return Builder::choice('type')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->enableSearch()
            ->label(__p('core::phrase.select_type'))
            ->options(SavedType::getFilterOptions());
    }

    protected function getSortTypeField(): AbstractField
    {
        return Builder::choice('sort_type')
            ->forBottomSheetForm()
            ->autoSubmit()
            ->label(__p('core::phrase.sort_label'))
            ->options([
                ['label' => __p('core::phrase.sort.latest'), 'value' => Browse::SORT_TYPE_DESC],
                ['label' => __p('core::phrase.sort.oldest'), 'value' => Browse::SORT_TYPE_ASC],
            ]);
    }
}
