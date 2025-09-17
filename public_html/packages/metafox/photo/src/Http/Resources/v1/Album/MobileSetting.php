<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1\Album;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Album Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 * @driverType resource-mobile
 * @driverName album
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->apiUrl('photo-album')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'is_featured' => ':is_featured',
            ])
            ->placeholder(__p('photo::phrase.search_albums'));

        $this->add('viewAll')
            ->apiUrl('photo-album')
            ->apiRules([
                'q'        => ['truthy', 'q'],
                'sort'     => ['includes', 'sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed']],
                'category' => ['numeric', 'category'],
                'when'     => ['includes', 'when', ['this_month', 'this_week', 'today']],
                'view'     => ['includes', 'view', ['my', 'friend', 'pending']],
            ]);

        $this->add('viewOnOwner')
            ->pageUrl('photo-album')
            ->apiUrl('photo-album')
            ->apiParams(['user_id' => ':id']);

        $this->add('viewItem')
            ->apiUrl('photo-album/:id')
            ->pageUrl('photo/album/:id');

        $this->add('deleteItem')
            ->apiUrl('photo-album/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('photo::phrase.delete_confirm_album'),
                ]
            );

        $this->add('addItem')
            ->pageUrl('photo/album/add')
            ->apiUrl('core/mobile/form/photo.album.store')
            ->apiParams(['owner_id' => ':id']);

        $this->add('editItem')
            ->pageUrl('photo/album/edit/:id')
            ->apiUrl('core/mobile/form/photo.album.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('photo/album/edit/:id')
            ->apiUrl('core/mobile/form/photo.album.update/:id');

        $this->add('sponsorItem')
            ->apiUrl('photo-album/sponsor/:id')
            ->asPatch();

        /**
         * @deprecated Remove in 5.2.0
         */
        $this->add('featureItem')
            ->apiUrl('photo-album/feature/:id');

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('photo/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('photo/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('addItems')
            ->apiUrl('core/mobile/form/photo.album.add_items/:id')
            ->asGet();

        $this->add('getAlbumItems')
            ->apiUrl('photo-album/items/:id')
            ->asGet();

        $this->add('addItemForm')
            ->apiUrl('core/form/photo_album.store/?owner_id=:id')
            ->asGet();

        $this->add('viewMyAlbums')
            ->apiUrl('photo-album')
            ->apiParams([
                'view' => 'my',
            ]);

        $this->add('searchGlobalPhotoAlbum')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'                        => 'photo_album',
                'q'                           => ':q',
                'owner_id'                    => ':owner_id',
                'when'                        => ':when',
                'related_comment_friend_only' => ':related_comment_friend_only',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
            ]);

        $this->add('searchInOwner')
            ->apiUrl('photo-album')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('photo::phrase.search_albums'));

        $this->add('sponsorItemInFeed')
            ->apiUrl('photo-album/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('chooseAvatarCoverAlbum')
            ->apiUrl('photo-album')
            ->apiParams([
                'sort'    => Browse::SORT_RECENT,
                'limit'   => 20,
                'user_id' => ':id',
            ]);

        $this->add('viewDetailMediaItem')
            ->apiUrl('photo-album/items/:id')
            ->apiParams([
                'media_id' => ':media_id',
            ]);
    }
}
