<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Blog\Http\Resources\v1\Blog;

use MetaFox\Blog\Support\Browse\Scopes\Blog\ViewScope;
use MetaFox\Platform\Resource\MobileSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Blog Mobile Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class MobileSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('blog')
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'sort'        => ['includes', 'sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed']],
                'tag'         => ['truthy', 'tag'], 'category_id' => ['truthy', 'category_id'],
                'when'        => ['includes', 'when', ['all', 'this_month', 'this_week', 'today']],
                'is_featured' => ['truthy', 'is_featured'],
                'view'        => [
                    'includes', 'view', [
                        Browse::VIEW_MY,
                        Browse::VIEW_FRIEND,
                        Browse::VIEW_PENDING,
                        Browse::VIEW_FEATURE,
                        Browse::VIEW_SPONSOR,
                        ViewScope::VIEW_DRAFT,
                        Browse::VIEW_SEARCH,
                        Browse::VIEW_MY_PENDING,
                    ],
                ],
            ])
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'is_featured' => ':is_featured',
                'view'        => ':view',
            ]);

        $this->add('viewMyBlogs')
            ->apiUrl('blog')
            ->apiParams(['view' => 'my']);

        $this->add('viewFriendBlogs')
            ->apiUrl('blog')
            ->apiParams(['view' => 'friend']);

        $this->add('viewDraftBlogs')
            ->apiUrl('blog')
            ->apiParams(['view' => 'draft']);

        $this->add('viewPendingBlogs')
            ->apiUrl('blog')
            ->apiParams(['view' => 'pending']);

        $this->add('viewMyPendingBlogs')
            ->apiUrl('blog')
            ->apiParams([
                'view' => 'my_pending',
            ]);

        $this->add('viewOnOwner')
            ->apiUrl('blog')
            ->apiParams([
                'user_id' => ':id',
            ]);

        $this->add('viewItem')
            ->apiUrl('blog/:id')
            ->urlParams(['id' => ':id']);

        $this->add('deleteItem')
            ->apiUrl('blog/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('blog::phrase.delete_confirm'),
                ]
            );

        $this->add('editItem')
            ->apiUrl('core/mobile/form/blog.blog.update/:id');

        $this->add('editFeedItem')
            ->apiUrl('core/mobile/form/blog.blog.update/:id');

        $this->add('addItem')
            ->apiUrl('core/mobile/form/blog.blog.store')
            ->apiParams(['owner_id' => ':id']);

        $this->add('publishBlog')
            ->apiUrl('blog/publish/:id')
            ->asPatch()
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('blog::phrase.publish_blog_confirm'),
            ]);

        $this->add('approveItem')
            ->apiUrl('blog/approve/:id')
            ->asPatch();

        $this->add('sponsorItem')
            ->apiUrl('blog/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('blog/sponsor-in-feed/:id')
            ->asPatch();

        /**
         * @deprecated Remove in 5.2.0
         */
        $this->add('featureItem')
            ->apiUrl('blog/feature/:id');

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('blog/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('blog/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('searchItem')
            ->apiUrl('blog')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => 'search',
                'category_id' => ':category_id',
                'is_featured' => ':is_featured',
            ])
            ->placeholder(__p('blog::phrase.search_blogs'));

        $this->add('searchGlobalBlog')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'                        => 'blog',
                'q'                           => ':q',
                'owner_id'                    => ':owner_id',
                'when'                        => ':when',
                'related_comment_friend_only' => ':related_comment_friend_only',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
            ]);

        $this->add('searchInOwner')
            ->apiUrl('blog')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('blog::phrase.search_blogs'));

        $this->add('sponsorItem')
            ->apiUrl('blog/sponsor/:id');
    }
}
