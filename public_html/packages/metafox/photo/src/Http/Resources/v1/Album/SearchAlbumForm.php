<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1\Album;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Html\Choice;
use MetaFox\Form\Html\SearchBoxField;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @driverName photo_album.search
 * @preload    1
 */
class SearchAlbumForm extends AbstractForm
{
    public function prepare(): void
    {
        $this->config([
            'action'           => '/photo/albums/search',
            'acceptPageParams' => ['q', 'sort', 'when', 'from', 'category', 'is_featured'],
        ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            new SearchBoxField(
                ['name' => 'q', 'placeholder' => __p('photo::phrase.search_albums'), 'className' => 'mb2']
            ),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['category', 'q', 'view']),
            new Choice([
                'name'    => 'sort',
                'label'   => 'Sort',
                'margin'  => 'normal',
                'options' => [
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
                ],
            ]),
            new Choice([
                'name'    => 'when',
                'label'   => 'When',
                'margin'  => 'normal',
                'size'    => 'large',
                'options' => [
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
                ],
            ]),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
        );
    }
}
