<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1\Album;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Album Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('photo/albums');

        $this->add('searchItem')
            ->pageUrl('photo/albums/search')
            ->placeholder(__p('photo::phrase.search_albums'));

        $this->add('viewAll')
            ->apiUrl('photo-album')
            ->apiRules(['q' => ['truthy', 'q'], 'sort' => ['includes', 'sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed']], 'category' => ['numeric', 'category'], 'is_featured' => ['truthy', 'is_featured'], 'when' => ['includes', 'when', ['this_month', 'this_week', 'today']], 'view' => ['includes', 'view', ['my', 'friend', 'pending']]]);

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
            ->apiUrl('core/form/photo_album.store');

        $this->add('editItem')
            ->pageUrl('photo/album/edit/:id')
            ->apiUrl('core/form/photo_album.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('photo/album/edit/:id')
            ->apiUrl('core/form/photo_album.update/:id');

        $this->add('sponsorItem')
            ->apiUrl('photo-album/sponsor/:id')
            ->asPatch();

        $this->add('approveItem')
            ->apiUrl('photo-album/approve/:id')
            ->asPatch();

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('photo-album/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('photo-album/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('addPhotos')
            ->apiUrl('core/form/photo_album.add_photos/:id')
            ->asGet();

        $this->add('getAlbumItems')
            ->apiUrl('photo-album/items/:id')
            ->asGet();

        $this->add('addItemForm')
            ->apiUrl('core/form/photo_album.store/?owner_id=:id')
            ->asGet();

        $this->add('selectFromGroupPhotos')
            ->apiUrl('photo')
            ->asGet()
            ->apiParams(['user_id' => ':user_id', 'view' => 'no_album']);

        $this->add('selectFromMyPhotos')
            ->apiUrl('photo')
            ->asGet()
            ->apiParams(['view' => 'no_album']);

        $this->add('sponsorItemInFeed')
            ->apiUrl('photo-album/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('chooseAvatarCoverAlbum')
            ->apiUrl('photo-album')
            ->apiParams([
                'sort'    => Browse::SORT_RECENT,
                'limit'   => 20,
                'user_id' => ':user_id',
            ]);

        $this->add('viewDetailMediaItem')
            ->apiUrl('photo-album/items/:id')
            ->apiParams([
                'media_id' => ':media_id',
            ]);
    }
}
