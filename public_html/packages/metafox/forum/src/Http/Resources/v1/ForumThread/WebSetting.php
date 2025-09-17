<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;

/**
 *--------------------------------------------------------------------------
 * ForumThread Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('forum-thread')
            ->apiParams([
                'sort'      => ':sort',
                'sort_type' => ':sort_type',
                'forum_id'  => ':id',
            ])
            ->apiRules([
                'view'      => ['includes', 'view', ThreadViewScope::getAllowView()],
                'sort'      => ['includes', 'sort', ThreadSortScope::getAllowSort()],
                'sort_type' => ['includes', 'sort_type', SortScope::getAllowSortType()],
                'when'      => ['includes', 'when', WhenScope::getAllowWhen()],
                'forum_id'  => ['truthy', 'forum_id'],
            ]);

        $this->add('homePage')
            ->pageUrl('forum');

        $this->add('viewItem')
            ->pageUrl('forum/thread/:id')
            ->apiUrl('forum-thread/:id');

        $this->add('subscribeItem')
            ->apiUrl('forum-thread/subscribe/:id')
            ->asPatch()
            ->apiParams(['is_subscribed' => ':is_subscribed']);

        $this->add('addItem')
            ->pageUrl('forum/thread/add')
            ->apiUrl('core/form/forum.forum_thread.store')
            ->apiParams(['forum_id' => ':id'])
            ->pageParams(['forum_id' => ':id']);

        $this->add('editItem')
            ->pageUrl('forum/thread/edit/:id')
            ->apiUrl('core/form/forum.forum_thread.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('forum/thread/edit/:id')
            ->apiUrl('core/form/forum.forum_thread.update/:id');

        $this->add('moveItem')
            ->apiUrl('forum-thread/move/form/:id');

        $this->add('stickItem')
            ->apiUrl('forum-thread/stick/:id')
            ->asPatch()
            ->apiParams(['is_sticked' => ':is_sticked']);

        $this->add('closeItem')
            ->apiUrl('forum-thread/close/:id')
            ->asPatch()
            ->apiParams(['is_closed' => ':is_closed'])
            ->confirm([
                'title'   => __p('forum::phrase.close_thread'),
                'message' => __p('forum::phrase.close_thread_confirmation'),
            ]);

        $this->add('reopenItem')
            ->apiUrl('forum-thread/close/:id')
            ->asPatch()
            ->apiParams(['is_closed' => ':is_closed']);

        $this->add('deleteItem')
            ->apiUrl('forum-thread/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('core::phrase.delete_confirm'),
                ]
            );

        $this->add('sponsorItem')
            ->apiUrl('forum-thread/sponsor/:id')
            ->asPatch();

        $this->add('approveItem')
            ->apiUrl('forum-thread/approve/:id')
            ->asPatch();

        $this->add('sponsorItemInFeed')
            ->apiUrl('forum-thread/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('copyItem')
            ->apiUrl('forum-thread/copy/form/:id');

        $this->add('searchItem')
            ->pageUrl('forum/search')
            ->pageParams(['item_type' => ForumThread::ENTITY_TYPE])
            ->placeholder(__p('forum::web.search_discussions'));

        $this->add('updateLastRead')
            ->apiUrl('forum-thread/last-read/:id')
            ->asPatch()
            ->apiParams(['post_id' => ':id']);

        $this->add('mergeItem')
            ->apiUrl('forum-thread/merge/form/:id');

        $this->add('viewPosters')
            ->apiUrl('forum-post/posters')
            ->apiParams([
                'thread_id' => ':item_id',
            ]);

        $this->add('viewProfile')
            ->apiUrl('forum-thread')
            ->apiParams([
                'user_id' => ':id',
            ]);

        $this->add('viewMyPendingThread')
            ->apiUrl('forum-thread')
            ->apiParams([
                'view' => Browse::VIEW_MY_PENDING,
            ]);

        $this->add('viewLatestThreads')
            ->apiUrl('forum-thread')
            ->apiParams([
                'view' => Browse::VIEW_LATEST,
            ]);

        $this->add('viewLatestPosts')
            ->apiUrl('forum-thread')
            ->apiParams([
                'view'     => ThreadViewScope::VIEW_LATEST_POSTS,
                'forum_id' => ':id',
            ]);
    }
}
