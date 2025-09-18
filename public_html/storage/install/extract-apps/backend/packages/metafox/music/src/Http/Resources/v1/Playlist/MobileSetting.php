<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use MetaFox\Music\Support\Browse\Scopes\Playlist\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Playlist\ViewScope;
use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;
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
 * @preload
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('music/playlist');

        $this->add('searchItem')
            ->apiUrl('music/playlist')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => 'search',
                'category_id' => ':category_id',
                'genre_id'    => ':genre_id',
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

        $this->add('viewItem')
            ->pageUrl('music/playlist/album/:id')
            ->apiUrl('music/playlist/:id');

        $this->add('viewMyPlaylists')
            ->apiUrl('music/playlist')
            ->apiParams([
                'view' => 'my',
            ]);

        $this->add('addItem')
            ->pageUrl('music/playlist/add')
            ->apiUrl('core/mobile/form/music.music_playlist.store');

        $this->add('addSong')
            ->asPost()
            ->apiUrl('music/playlist/add-song')
            ->apiParams([
                'item_id'      => ':item_id',
                'playlist_ids' => ':playlist_ids',
            ]);

        $this->add('editItem')
            ->pageUrl('music/playlist/edit/:id')
            ->apiUrl('core/mobile/form/music.music_playlist.update/:id');

        $this->add('approveItem')
            ->apiUrl('music/playlist/approve/:id')
            ->asPatch();

        $this->add('deleteItem')
            ->apiUrl('music/playlist/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('music::phrase.delete_confirm', ['item_type' => 'music_playlist']),
                ]
            );
        $this->add('searchGlobalPlaylist')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'       => 'music_playlist',
                'q'          => ':q',
                'is_hashtag' => ':is_hashtag',
            ]);
    }
}
