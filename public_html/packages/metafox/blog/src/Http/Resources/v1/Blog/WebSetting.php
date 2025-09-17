<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Blog\Http\Resources\v1\Blog;

use MetaFox\Blog\Support\Browse\Scopes\Blog\ViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Blog Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class BlogWebSetting.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->pageUrl('blog/search')
            ->pageParams(['view' => Browse::VIEW_SEARCH])
            ->placeholder(__p('blog::phrase.search_blogs'));

        $this->add('homePage')
            ->pageUrl('blog');

        $this->add('viewAll')
            ->pageUrl('blog/all')
            ->apiUrl('blog')
            ->apiRules([
                'q' => [
                    'truthy', 'q',
                ], 'sort' => ['includes', 'sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed']],
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
            ]);

        $this->add('viewItem')
            ->pageUrl('blog/:id')
            ->apiUrl('blog/:id');

        $this->add('deleteItem')
            ->apiUrl('blog/:id')
            ->pageUrl('blog/all')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('blog::phrase.delete_confirm'),
                ]
            );

        $this->add('editItem')
            ->pageUrl('blog/edit/:id')
            ->apiUrl('core/form/blog.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('blog/edit/:id')
            ->apiUrl('core/form/blog.update/:id');

        $this->add('addItem')
            ->pageUrl('blog/add')
            ->apiUrl('core/form/blog.store');

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
    }
}
