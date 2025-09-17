<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Http\Resources\v1\Song;

use MetaFox\Form\Constants;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Support\Browse\Scopes\Song\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Song\ViewScope;
use MetaFox\Music\Support\Facades\Music;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;

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
        $this->add('homePage')
            ->pageUrl('music/song');

        $this->add('searchItem')
            ->pageUrl('music/search')
            ->pageParams([
                'entity_type' => Music::convertEntityType(Song::ENTITY_TYPE),
                'view'        => 'search',
            ])
            ->apiUrl('music/search')
            ->placeholder(__p('music::phrase.search_songs'));

        $this->add('viewSearchAll')
            ->apiUrl('music')
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'sort'        => [
                    'includes', 'sort', SortScope::getAllowSort(),
                ],
                'when'        => [
                    'includes', 'when', WhenScope::getAllowWhen(),
                ],
                'view'        => [
                    'includes', 'view', ViewScope::getAllowView(),
                ],
                'genre_id'    => ['truthy', 'genre_id'],
                'entity_type' => ['truthy', 'entity_type'],
            ]);

        $this->add('viewAll')
            ->apiUrl('music/song')
            ->apiRules([
                'q'        => ['truthy', 'q'],
                'sort'     => [
                    'includes', 'sort', SortScope::getAllowSort(),
                ],
                'when'     => [
                    'includes', 'when', WhenScope::getAllowWhen(),
                ],
                'view'     => [
                    'includes', 'view', ViewScope::getAllowView(),
                ],
                'genre_id' => ['truthy', 'genre_id'],
            ]);

        $this->add('viewItem')
            ->pageUrl('music/song/:id')
            ->apiUrl('music/song/:id');

        $this->add('addItem')
            ->pageUrl('music/song/add')
            ->apiUrl('core/form/music_song.store');

        $this->add('sponsorItem')
            ->apiUrl('music/song/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('music/song/sponsor-in-feed/:id')
            ->asPatch();

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

        $this->add('editItem')
            ->pageUrl('music/song/edit/:id')
            ->apiUrl('core/form/music_song.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('music/song/edit/:id')
            ->apiUrl('core/form/music_song.update/:id');

        $this->add('downloadItem')
            ->apiUrl('music/song/download/:id');

        $this->add('addToPlaylist')
            ->apiMethod(Constants::METHOD_GET)
            ->apiUrl('core/form/music.music_song.add_to_playlist')
            ->apiParams([
                'item_id' => ':item_id',
            ]);

        $this->add('viewFeatures')
            ->apiUrl('music/song')
            ->apiParams([
                'view' => Browse::VIEW_FEATURE,
            ]);

        $this->add('viewPopular')
            ->apiUrl('music/song')
            ->apiParams([
                'sort' => SortScope::SORT_MOST_PLAYED,
            ]);

        $this->add('updateTotalPlayItem')
            ->apiUrl('music/song/:id/statistic/total-play')
            ->asPatch();

        $this->add('removeFromPlaylist')
            ->apiUrl('music/song/:id/remove-from-playlist/:playlist_id')
            ->asPatch();
    }
}
