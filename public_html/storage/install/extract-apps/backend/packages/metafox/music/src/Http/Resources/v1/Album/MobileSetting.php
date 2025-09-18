<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Http\Resources\v1\Album;

use MetaFox\Music\Support\Browse\Scopes\Album\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Album\ViewScope;
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
            ->pageUrl('music/album');

        $this->add('searchItem')
            ->apiUrl('music/album')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => 'search',
                'category_id' => ':category_id',
                'genre_id'    => ':genre_id',
                'is_featured' => ':is_featured',
            ])
            ->placeholder(__p('music::phrase.search_albums'));

        $this->add('viewAll')
            ->apiUrl('music/album')
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
            ->pageUrl('music/album')
            ->apiUrl('music/album')
            ->apiParams(['user_id' => ':id']);

        $this->add('viewItem')
            ->pageUrl('music/album/album/:id')
            ->apiUrl('music/album/:id');

        $this->add('viewMyAlbums')
            ->apiUrl('music/album')
            ->apiParams([
                'view' => 'my',
            ]);

        $this->add('addItem')
            ->pageUrl('music/album/add')
            ->apiUrl('core/mobile/form/music.music_album.store');

        $this->add('editItem')
            ->pageUrl('music/album/edit/:id')
            ->apiUrl('core/mobile/form/music.music_album.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('music/album/edit/:id')
            ->apiUrl('core/mobile/form/music.music_album.update/:id');

        $this->add('sponsorItem')
            ->apiUrl('music/album/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('music/album/sponsor-in-feed/:id')
            ->asPatch();

        /*
        * @deprecated Remove in 5.2.0
        */
        $this->add('featureItem')
            ->apiUrl('music/album/feature/:id');

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

        $this->add('approveItem')
            ->apiUrl('music/album/approve/:id')
            ->asPatch();

        $this->add('deleteItem')
            ->apiUrl('music/album/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('music::phrase.delete_confirm', ['item_type' => 'music_album']),
                ]
            );
        $this->add('searchGlobalAlbum')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'       => 'music_album',
                'q'          => ':q',
                'is_hashtag' => ':is_hashtag',
            ]);

        $this->add('searchInOwner')
            ->apiUrl('music/album')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('music::phrase.search_albums'));
    }
}
