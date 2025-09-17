<?php

namespace MetaFox\Blog\Http\Resources\v1\Blog;

use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @driverName blog.search
 * @driverType form
 * @preload    1
 */
class SearchBlogForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/blog/search')
            ->acceptPageParams(['q', 'sort', 'when', 'category_id', 'returnUrl', 'is_featured'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('blog::phrase.search_blogs'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['view', 'category_id', 'q']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->marginNormal()
                ->sizeLarge()
                ->options([['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_LATEST], ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED], ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED], ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED]]),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->sizeLarge()
                ->options([['label' => __p('core::phrase.when.all'), 'value' => Browse::WHEN_ALL], ['label' => __p('core::phrase.when.this_month'), 'value' => Browse::WHEN_THIS_MONTH], ['label' => __p('core::phrase.when.this_week'), 'value' => Browse::WHEN_THIS_WEEK], ['label' => __p('core::phrase.when.today'), 'value' => Browse::WHEN_TODAY]]),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
            Builder::filterCategory('category_id')
                ->label(__p('core::phrase.categories'))
                ->apiUrl('/blog-category')
                ->marginNormal()
                ->sizeLarge(),
        );
    }
}
