<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use MetaFox\Form\Builder;
use MetaFox\Form\Html\BuiltinSearchForm;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Quiz\Models\Quiz as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SearchQuizForm extends BuiltinSearchForm
{
    protected function prepare(): void
    {
        $this->acceptPageParams(['q', 'sort', 'when', 'view', 'is_featured'])
            ->action('/quiz/search')
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('quiz::phrase.search_quizzes'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['q', 'view']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->marginNormal()
                ->options([
                    ['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_LATEST],
                    ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED],
                    ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED],
                    ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED],
                ]),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->sizeLarge()
                ->options([
                    ['label' => __p('core::phrase.when.all'), 'value' => Browse::WHEN_ALL],
                    ['label' => __p('core::phrase.when.this_month'), 'value' => Browse::WHEN_THIS_MONTH],
                    ['label' => __p('core::phrase.when.this_week'), 'value' => Browse::WHEN_THIS_WEEK],
                    ['label' => __p('core::phrase.when.today'), 'value' => Browse::WHEN_TODAY],
                ]),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
        );
    }
}
