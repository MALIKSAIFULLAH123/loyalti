<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Forum\Models\Forum as Model;
use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchForumThreadForm.
 * @property ?Model $resource
 */
class SearchHomepageThreadForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/forum-thread/')
            ->acceptPageParams(['sort'])
            ->setValue([
                'sort' => ThreadSortScope::SORT_LATEST_DISCUSSED,
            ])->submitOnValueChanged();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::dropdown('sort')
                ->sizeSmall()
                ->marginNone()
                ->disableClearable()
                ->freeSolo(false)
                ->sx(['width' => 'auto'])
                ->options($this->getSortThreadOptions())
                ->className('select-quick-sort')
                ->setAttribute('placement', 'right'),
        );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getSortThreadOptions(): array
    {
        return [
            [
                'label' => __p('forum::phrase.latest_discussed'), 'value' => ThreadSortScope::SORT_LATEST_DISCUSSED,
            ],
            [
                'label' => __p('forum::phrase.recent_post'), 'value' => ThreadSortScope::SORT_RECENT_POST,
            ],
            [
                'label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED,
            ],
            [
                'label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED,
            ],
        ];
    }
}
