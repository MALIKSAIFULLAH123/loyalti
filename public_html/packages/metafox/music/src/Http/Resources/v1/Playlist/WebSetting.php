<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Support\Browse\Scopes\Playlist\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Playlist\ViewScope;
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
                'entity_type' => Music::convertEntityType(Playlist::ENTITY_TYPE),
                'view'        => 'search',
            ])
            ->placeholder(__p('music::phrase.search_playlists'));

        $this->add('viewAll')
            ->apiUrl('music/playlist')
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

        $this->add('viewMy')
            ->apiUrl('music/playlist')
            ->apiParams(['view' => Browse::VIEW_MY]);

        $this->add('viewItem')
            ->pageUrl('music/playlist/:id')
            ->apiUrl('music/playlist/:id');

        $this->add('getAlbumItems')
            ->apiUrl('music/playlist/items/:id')
            ->asGet();

        $this->add('addItem')
            ->pageUrl('music/playlist/add')
            ->apiUrl('core/form/music_playlist.store');

        $this->add('deleteItem')
            ->apiUrl('music/playlist/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('music::phrase.delete_confirm', ['item_type' => 'music_playlist']),
                ]
            );

        $this->add('editItem')
            ->pageUrl('music/playlist/edit/:id')
            ->apiUrl('core/form/music_playlist.update/:id');

        $this->add('homePage')
            ->pageUrl('music/playlists');
    }
}
