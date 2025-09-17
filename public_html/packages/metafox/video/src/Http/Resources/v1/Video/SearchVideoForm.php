<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Http\Resources\v1\Video;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @preload 1
 */
class SearchVideoForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/video/search')
            ->acceptPageParams(['q', 'sort', 'when', 'category_id', 'view', 'is_featured'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::searchBox('q')
                    ->placeholder(__p('video::phrase.search_videos'))
                    ->className('mb2'),
                Builder::clearSearch()
                    ->label(__p('core::phrase.reset'))
                    ->align('right')
                    ->excludeFields(['category_id', 'q', 'view']),
                Builder::choice('sort')
                    ->label(__p('core::phrase.sort_label'))
                    ->marginNormal()
                    ->options([
                        [
                            'label' => __p('core::phrase.sort.recent'),
                            'value' => Browse::SORT_RECENT,
                        ],
                        [
                            'label' => __p('core::phrase.sort.most_viewed'),
                            'value' => Browse::SORT_MOST_VIEWED,
                        ],
                        [
                            'label' => __p('core::phrase.sort.most_liked'),
                            'value' => Browse::SORT_MOST_LIKED,
                        ],
                        [
                            'label' => __p('core::phrase.sort.most_discussed'),
                            'value' => Browse::SORT_MOST_DISCUSSED,
                        ],
                        [
                            'label' => __p('core::phrase.sort.a_to_z'),
                            'value' => Browse::SORT_A_TO_Z,
                        ],
                        [
                            'label' => __p('core::phrase.sort.z_to_a'),
                            'value' => Browse::SORT_Z_TO_A,
                        ],
                    ]),
                Builder::choice('when')
                    ->label(__p('core::phrase.when_label'))
                    ->marginNormal()
                    ->options([
                        [
                            'label' => __p('core::phrase.when.all'),
                            'value' => Browse::WHEN_ALL,
                        ],
                        [
                            'label' => __p('core::phrase.when.this_month'),
                            'value' => Browse::WHEN_THIS_MONTH,
                        ],
                        [
                            'label' => __p('core::phrase.when.this_week'),
                            'value' => Browse::WHEN_THIS_WEEK,
                        ],
                        [
                            'label' => __p('core::phrase.when.today'),
                            'value' => Browse::WHEN_TODAY,
                        ],
                    ]),
                Builder::switch('is_featured')
                    ->label(__p('core::phrase.featured')),
                Builder::filterCategory('category_id')
                    ->label(__p('core::phrase.categories'))
                    ->apiUrl('/video/category'),
            );
    }
}
