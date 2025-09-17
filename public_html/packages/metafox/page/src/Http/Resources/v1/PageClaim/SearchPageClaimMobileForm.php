<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Http\Resources\v1\PageClaim;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Page\Support\Facade\PageClaim as PageClaimFacade;
use MetaFox\Platform\Support\Browse\Browse;

class SearchPageClaimMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/page-claim/search')
            ->acceptPageParams(['q', 'sort', 'when', 'category_id', 'status'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);

        $basic->addFields(
            $this->getSearchFields()
                ->placeholder(__p('page::phrase.search_page_claims')),
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
                ->targets(['sort', 'when', 'category_id', 'status']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('page::phrase.search_page_claims'))
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->autoSubmit()
                ->forBottomSheetForm()
                ->options($this->getSortOptions()),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->autoSubmit()
                ->forBottomSheetForm()
                ->options($this->getWhenOptions()),
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->autoSubmit()
                ->forBottomSheetForm()
                ->options(PageClaimFacade::getAllowStatusOptions()),
            Builder::autocomplete('category_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->label(__p('core::phrase.categories'))
                ->searchEndpoint('/page/category')
                ->searchParams(['level' => 0]),
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->showWhen(['truthy', 'filters'])
                ->targets(['sort', 'when', 'category_id', 'status']),
            Builder::choice('sort')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.sort_label'))
                ->showWhen(['truthy', 'filters'])
                ->options($this->getSortOptions()),
            Builder::choice('when')
                ->autoSubmit()
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.when_label'))
                ->showWhen(['truthy', 'filters'])
                ->options($this->getWhenOptions()),
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->forBottomSheetForm()
                ->useOptionContext()
                ->variant('standard-inlined')
                ->showWhen(['truthy', 'filters'])
                ->options(PageClaimFacade::getAllowStatusOptions()),
            Builder::autocomplete('category_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.categories'))
                ->showWhen(['truthy', 'filters'])
                ->searchEndpoint('/page/category')
                ->searchParams(['level' => 0]),
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
            ['label' => __p('core::phrase.sort.latest'), 'value' => 'recent'],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => 'most_member'],
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
