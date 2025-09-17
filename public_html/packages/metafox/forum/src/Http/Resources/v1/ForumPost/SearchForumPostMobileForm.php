<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Forum\Models\Forum as Model;
use MetaFox\Forum\Policies\ForumPolicy;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchForumPostMobileForm.
 * @property ?Model $resource
 */
class SearchForumPostMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->action('/forum/search')
            ->acceptPageParams(['q', 'sort', 'when', 'forum_id', 'item_type', 'returnUrl', 'view'])
            ->setValue([
                'item_type' => ForumSupport::SEARCH_BY_POST,
                'view'      => Browse::VIEW_SEARCH,
                'sort'      => Browse::SORT_RECENT,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);

        $basic->addFields(
            Builder::text('q')
                ->forBottomSheetForm('SFSearchBox')
                ->delayTime(200)
                ->placeholder(__p('forum::web.search_posts'))
                ->className('mb2'),
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
                ->targets(['forum_id', 'sort', 'when']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('forum::web.search_posts')),
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $canViewForum = policy_check(ForumPolicy::class, 'viewAny', user());
        $section->addFields(
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_by'))
                ->forBottomSheetForm()
                ->autoSubmit()
                ->options($this->getSortPostOptions()),
            Builder::choice('when')
                ->label(__p('forum::phrase.when'))
                ->forBottomSheetForm()
                ->autoSubmit()
                ->options($this->getWhenOptions()),
            $canViewForum ? Builder::autocomplete('forum_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->label(__p('forum::web.communities'))
                ->searchEndpoint('/forum/option') : null,
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $canViewForum = policy_check(ForumPolicy::class, 'viewAny', user());
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->showWhen(['truthy', 'filters'])
                ->targets(['forum_id', 'item_type', 'sort', 'when']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_by'))
                ->forBottomSheetForm()
                ->autoSubmit()
                ->showWhen(['truthy', 'filters'])
                ->variant('standard-inlined')
                ->options($this->getSortPostOptions()),
            Builder::choice('when')
                ->label(__p('forum::phrase.when'))
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters'])
                ->variant('standard-inlined')
                ->autoSubmit()
                ->options($this->getWhenOptions()),
            $canViewForum ? Builder::autocomplete('forum_id')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->showWhen(['truthy', 'filters'])
                ->variant('standard-inlined')
                ->label(__p('forum::web.communities'))
                ->searchEndpoint('/forum/option') : null,
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }

    protected function getSortPostOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_RECENT,
            ],
            [
                'label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED,
            ],
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
}
