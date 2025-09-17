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
class SearchForumThreadForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/forum-thread/')
            ->acceptPageParams(['sort', 'sort_type'])
            ->setValue([
                'sort'      => ThreadSortScope::SORT_DISCUSSED,
                'sort_type' => Browse::SORT_TYPE_DESC,
            ])->submitOnValueChanged();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();

        $basic->addFields(
            Builder::dropdown('sort')
                ->label(__p('forum::phrase.sort.sort_by'))
                ->disableClearable()
                ->freeSolo(false)
                ->sxFieldWrapper(['width' => 200])
                ->options($this->getSortThreadOptions()),
            Builder::dropdown('sort_type')
                ->label('')
                ->disableClearable()
                ->freeSolo(false)
                ->sxFieldWrapper(['width' => 200])
                ->options($this->getSortTypeOptions()),
        );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getSortTypeOptions(): array
    {
        return [
            [
                'label' => __p('forum::phrase.ascending'),
                'value' => Browse::SORT_TYPE_ASC,
            ],
            [
                'label' => __p('forum::phrase.descending'),
                'value' => Browse::SORT_TYPE_DESC,
            ],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function getSortThreadOptions(): array
    {
        return [
            [
                'label' => __p('forum::phrase.sort.discussed'),
                'value' => ThreadSortScope::SORT_DISCUSSED,
            ],
            [
                'label' => __p('forum::phrase.sort.last_post'),
                'value' => ThreadSortScope::SORT_LAST_POST,
            ],
            [
                'label' => __p('forum::phrase.sort.title'),
                'value' => ThreadSortScope::SORT_TITLE,
            ],
            [
                'label' => __p('forum::phrase.sort.replies'),
                'value' => ThreadSortScope::SORT_REPLIES,
            ],
        ];
    }
}
