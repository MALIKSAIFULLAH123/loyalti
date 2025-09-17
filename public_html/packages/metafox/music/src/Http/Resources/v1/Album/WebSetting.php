<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Http\Resources\v1\Album;

use MetaFox\Music\Models\Album;
use MetaFox\Music\Support\Browse\Scopes\Album\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Album\ViewScope;
use MetaFox\Music\Support\Facades\Music;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Song Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 *
 * @preload
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->pageUrl('music/search')
            ->pageParams([
                'entity_type' => Music::convertEntityType(Album::ENTITY_TYPE),
                'view'        => 'search',
            ])
            ->placeholder(__p('music::phrase.search_albums'));

        $this->add('viewAll')
            ->apiUrl('music/album')
            ->apiRules([
                'q'    => ['truthy', 'q'],
                'sort' => [
                    'includes', 'sort', SortScope::getAllowSort(),
                ],
                'when' => [
                    'includes', 'when', [
                        Browse::WHEN_ALL,
                        Browse::WHEN_THIS_MONTH,
                        Browse::WHEN_THIS_WEEK,
                        Browse::WHEN_TODAY,
                    ],
                ],
                'view' => [
                    'includes', 'view', ViewScope::getAllowView(),
                ],
            ]);

        $this->add('viewItem')
            ->pageUrl('music/album/:id')
            ->apiUrl('music/album/:id');

        $this->add('getAlbumItems')
            ->apiUrl('music/album/items/:id')
            ->asGet();

        $this->add('addItem')
            ->pageUrl('music/album/add')
            ->apiUrl('core/form/music_album.store');

        $this->add('sponsorItem')
            ->apiUrl('music/album/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('music/album/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('music/album/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('music/album/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('deleteItem')
            ->apiUrl('music/album/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('music::phrase.delete_confirm', ['item_type' => 'music_album']),
                ]
            );

        $this->add('editItem')
            ->pageUrl('music/album/edit/:id')
            ->apiUrl('core/form/music_album.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('music/album/edit/:id')
            ->apiUrl('core/form/music_album.update/:id');

        $this->add('viewFeatures')
            ->apiUrl('music/album')
            ->apiParams([
                'view' => Browse::VIEW_FEATURE,
            ]);

        $this->add('viewPopular')
            ->apiUrl('music/album')
            ->apiParams([
                'sort' => SortScope::SORT_MOST_PLAYED,
            ]);

        $this->add('homePage')
            ->pageUrl('music/albums');
    }
}
