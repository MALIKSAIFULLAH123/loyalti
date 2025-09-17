<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use MetaFox\Photo\Support\Browse\Scopes\Photo\ViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Photo Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 * @preload
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('photo');

        $this->add('searchItem')
            ->pageUrl('photo/search')
            ->placeholder(__p('photo::phrase.search_photos'));

        $this->add('viewAll')
            ->apiUrl('photo')
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'sort'        => [
                    'includes',
                    'sort',
                    [
                        Browse::SORT_RECENT, Browse::SORT_MOST_LIKED,
                        Browse::SORT_MOST_VIEWED, Browse::SORT_MOST_DISCUSSED,
                        Browse::SORT_A_TO_Z, Browse::SORT_Z_TO_A,
                    ],
                ],
                'category_id' => ['truthy', 'category_id'],
                'when'        => [
                    'includes',
                    'when',
                    [
                        Browse::WHEN_ALL, Browse::WHEN_THIS_MONTH,
                        Browse::WHEN_THIS_WEEK, Browse::WHEN_TODAY,
                    ],
                ],
                'is_featured' => ['truthy', 'is_featured'],
                'view'        => [
                    'includes',
                    'view',
                    [
                        Browse::VIEW_MY,
                        Browse::VIEW_FRIEND,
                        Browse::VIEW_PENDING,
                        Browse::VIEW_SEARCH,
                        Browse::VIEW_MY_PENDING,
                        ViewScope::VIEW_NO_ALBUM,
                        ViewScope::VIEW_TAG_WALL,
                    ],
                ],
            ]);

        $this->add('viewItem')
            ->pageUrl('photo/:id')
            ->apiUrl('photo/:id');

        $this->add('deleteItem')
            ->apiUrl('photo/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('photo::phrase.delete_confirm'),
                ]
            );

        $this->add('addItem')
            ->pageUrl('photo/add')
            ->apiUrl('core/form/photo.upload');

        $this->add('editItem')
            ->pageUrl('photo/edit/:id')
            ->apiUrl('core/form/photo.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('photo/edit/:id')
            ->apiUrl('core/form/photo.update/:id');

        $this->add('sponsorItem')
            ->apiUrl('photo/sponsor/:id');

        $this->add('approveItem')
            ->apiUrl('photo/approve/:id')
            ->asPatch();

        $this->add('sponsorItemInFeed')
            ->apiUrl('photo/sponsor-in-feed/:id')
            ->asPatch();

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

        $this->add('downloadItem')
            ->apiUrl('/photo/download/:id');

        $this->add('makeAvatar')
            ->apiUrl('photo/profile-avatar/:id');

        $this->add('makeCover')
            ->apiUrl('photo/profile-cover/:id')
            ->apiParams(['user_id' => ':user_id'])
            ->asPut();

        $this->add('makeParentCover')
            ->apiUrl('photo/parent-cover/:id')
            ->asPut();

        $this->add('makeParentAvatar')
            ->apiUrl('photo/parent-avatar/:id')
            ->asPut();

        $this->add('viewPhotoSet')
            ->apiUrl('photo-set/:id');

        $this->add('tagFriend')
            ->apiUrl('photo-tag')
            ->asPost();

        $this->add('getTaggedFriends')
            ->apiUrl('photo-tag')
            ->asGet()
            ->apiParams(['item_id' => ':item_id']);

        $this->add('removeTaggedFriend')
            ->apiUrl('photo-tag/:id')
            ->asDelete();

        $this->add('chooseAvatarCoverPhoto')
            ->apiUrl('photo')
            ->apiParams([
                'sort'    => Browse::SORT_RECENT,
                'limit'   => 20,
                'user_id' => ':user_id',
            ]);

        $this->add('chooseAvatarCoverPhotoFromAlbum')
            ->apiUrl('photo')
            ->apiParams([
                'sort'     => Browse::SORT_RECENT,
                'limit'    => 20,
                'album_id' => ':album_id',
            ]);
    }
}
