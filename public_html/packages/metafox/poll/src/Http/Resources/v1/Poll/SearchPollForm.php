<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use MetaFox\Form\Builder;
use MetaFox\Form\Html\BuiltinSearchForm;
use MetaFox\Form\Html\SearchBoxField;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Poll\Models\Poll as Model;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @preload 1
 */
class SearchPollForm extends BuiltinSearchForm
{
    protected function prepare(): void
    {
        $this->action('/poll/search')
            ->acceptPageParams(['q', 'sort', 'when', 'returnUrl', 'view', 'is_featured'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('poll::phrase.search_polls'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['q', 'view']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->marginNormal()
                ->options(
                    [
                        ['label' => __p('core::phrase.sort.recent'), 'value' => 'latest'],
                        ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED],
                        ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED],
                        ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED],
                    ]
                ),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->sizeLarge()
                ->options(
                    [
                        ['label' => __p('core::phrase.when.all'), 'value' => Browse::WHEN_ALL],
                        ['label' => __p('core::phrase.when.this_month'), 'value' => Browse::WHEN_THIS_MONTH],
                        ['label' => __p('core::phrase.when.this_week'), 'value' => Browse::WHEN_THIS_WEEK],
                        ['label' => __p('core::phrase.when.today'), 'value' => Browse::WHEN_TODAY],
                    ]
                ),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
        );
    }
}
