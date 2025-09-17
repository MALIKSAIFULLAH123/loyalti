<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Http\Resources\v1\Song;

use MetaFox\Form\Constants;
use MetaFox\Music\Support\Browse\Scopes\Song\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Song\ViewScope;
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
            ->pageUrl('music/song');

        $this->add('searchItem')
            ->apiUrl('music/song')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => 'search',
                'category_id' => ':category_id',
                'genre_id'    => ':genre_id',
                'is_featured' => ':is_featured',
            ])
            ->placeholder(__p('music::phrase.search_songs'));

        $this->add('viewAll')
            ->apiUrl('music/song')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => ':view',
                'category_id' => ':category_id',
                'genre_id'    => ':genre_id',
                'is_featured' => ':is_featured',
            ])
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

        $this->add('viewOnOwner')
            ->pageUrl('music/song')
            ->apiUrl('music/song')
            ->apiParams(['user_id' => ':id']);

        $this->add('viewItem')
            ->pageUrl('music/song/:id')
            ->apiUrl('music/song/:id');

        $this->add('addItem')
            ->pageUrl('music/song/add')
            ->apiUrl('core/mobile/form/music.music_song.store');

        $this->add('editItem')
            ->pageUrl('music/song/edit/:id')
            ->apiUrl('core/mobile/form/music.music_song.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('music/song/edit/:id')
            ->apiUrl('core/mobile/form/music.music_song.update/:id');

        $this->add('sponsorItem')
            ->apiUrl('music/song/sponsor/:id');

        $this->add('viewMySongs')
            ->apiUrl('music/song')
            ->apiParams([
                'view' => 'my',
            ]);

        $this->add('viewFriendSongs')
            ->apiUrl('music/song')
            ->apiParams([
                'view' => 'friend',
            ]);

        $this->add('viewPendingSongs')
            ->apiUrl('music/song')
            ->apiParams([
                'view' => 'pending',
            ]);

        $this->add('viewMyPendingSongs')
            ->apiUrl('music/song')
            ->apiParams([
                'view' => 'my_pending',
            ]);

        $this->add('sponsorItemInFeed')
            ->apiUrl('music/song/sponsor-in-feed/:id')
            ->asPatch();

        /*
        * @deprecated Remove in 5.2.0
        */
        $this->add('featureItem')
            ->apiUrl('music/song/feature/:id');

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('music/song/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('music/song/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('approveItem')
            ->apiUrl('music/song/approve/:id')
            ->asPatch();

        $this->add('deleteItem')
            ->apiUrl('music/song/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('music::phrase.delete_confirm', ['item_type' => 'music_song']),
                ]
            );

        $this->add('downloadItem')
            ->apiUrl('music/song/download/:id');

        $this->add('addToPlaylist')
            ->apiMethod(Constants::METHOD_GET)
            ->apiUrl('core/mobile/form/music.music_song.add_to_playlist')
            ->apiParams([
                'item_id' => ':item_id',
            ]);

        $this->add('searchGlobalSong')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'       => 'music_song',
                'q'          => ':q',
                'is_hashtag' => ':is_hashtag',
            ]);

        $this->add('removeFromPlaylist')
            ->apiUrl('music/song/:id/remove-from-playlist/:playlist_id')
            ->asPatch();

        $this->add('updateTotalPlayItem')
            ->apiUrl('music/song/:id/statistic/total-play')
            ->asPatch();

        $this->add('searchInOwner')
            ->apiUrl('music/song')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('music::phrase.search_songs'));
    }
}
