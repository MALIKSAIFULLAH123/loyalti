<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;
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
 *
 * @preload
 * @driverType resource-mobile
 * @driverName photo
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('photo');

        $this->add('searchItem')
            ->apiUrl('photo')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'view'        => 'search',
                'is_featured' => ':is_featured',
            ])
            ->placeholder(__p('photo::phrase.search_photos'));

        $this->add('viewAll')
            ->apiUrl('photo')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'view'        => ':view',
                'is_featured' => ':is_featured',
            ])
            ->apiRules([
                'q'              => ['truthy', 'q'], 'sort' => [
                    'includes', 'sort', [
                        Browse::SORT_RECENT, Browse::SORT_MOST_LIKED, Browse::SORT_MOST_VIEWED,
                        Browse::SORT_MOST_DISCUSSED,
                        Browse::SORT_A_TO_Z, Browse::SORT_Z_TO_A,
                    ],
                ], 'category_id' => ['truthy', 'category_id'], 'when' => [
                    'includes', 'when',
                    [Browse::WHEN_ALL, Browse::WHEN_THIS_MONTH, Browse::WHEN_THIS_WEEK, Browse::WHEN_TODAY],
                ], 'view'        => ['includes', 'view', ['my', 'friend', 'pending']],
            ]);

        $this->add('viewOnOwner')
            ->pageUrl('photo')
            ->apiUrl('photo')
            ->apiParams(['user_id' => ':id']);

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
            ->apiUrl('core/mobile/form/photo.photo.upload')
            ->apiParams(['owner_id' => ':id']);

        $this->add('editItem')
            ->pageUrl('photo/edit/:id')
            ->apiUrl('core/mobile/form/photo.photo.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('photo/edit/:id')
            ->apiUrl('core/mobile/form/photo.photo.update/:id');

        $this->add('sponsorItem')
            ->apiUrl('photo/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('photo/sponsor-in-feed/:id')
            ->asPatch();

        /**
         * @deprecated Remove in 5.2.0
         */
        $this->add('featureItem')
            ->apiUrl('photo/feature/:id');

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
            ->apiUrl('photo/download/:id');

        $this->add('makeAvatar')
            ->apiUrl('photo/profile-avatar/:id');

        $this->add('makeCover')
            ->apiUrl('photo/profile-cover/:id')
            ->asPut();

        $this->add('makeParentCover')
            ->apiUrl('photo/parent-cover/:id')
            ->asPut();

        $this->add('makeParentAvatar')
            ->apiUrl('photo/parent-avatar/:id')
            ->asPut();

        $this->add('viewPhotoSet')
            ->apiUrl('photo-set/:id/items');

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

        $this->add('viewMyPhotos')
            ->apiUrl('photo')
            ->apiParams([
                'view' => 'my',
            ]);
        $this->add('viewFriendPhotos')
            ->apiUrl('photo')
            ->apiParams([
                'view' => 'friend',
            ]);
        $this->add('viewPendingPhotos')
            ->apiUrl('photo')
            ->apiParams([
                'view' => 'pending',
            ]);
        $this->add('viewMyPendingPhotos')
            ->apiUrl('photo')
            ->apiParams([
                'view' => 'my_pending',
            ]);

        $this->add('searchGlobalPhoto')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'                        => 'photo',
                'q'                           => ':q',
                'owner_id'                    => ':owner_id',
                'when'                        => ':when',
                'related_comment_friend_only' => ':related_comment_friend_only',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
            ]);

        $this->add('approveItem')
            ->apiUrl('photo/approve/:id')
            ->asPatch();

        $this->add('searchInOwner')
            ->apiUrl('photo')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('photo::phrase.search_photos'));

        $this->add('chooseAvatarCoverPhoto')
            ->apiUrl('photo')
            ->apiParams([
                'sort'    => Browse::SORT_RECENT,
                'limit'   => 20,
                'user_id' => ':id',
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
