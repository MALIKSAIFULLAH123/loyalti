<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope;
use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;
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
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('forum-thread')
            ->apiRules([
                'q'         => ['truthy', 'q'],
                'owner_id'  => ['truthy', 'owner_id'],
                'user_id'   => ['truthy', 'user_id'],
                'view'      => ['includes', 'view', ThreadViewScope::getAllowView()],
                'sort'      => ['includes', 'sort', ThreadSortScope::getAllowSort()],
                'sort_type' => ['includes', 'sort_type', SortScope::getAllowSortType()],
                'when'      => ['includes', 'when', WhenScope::getAllowWhen()],
                'forum_id'  => ['truthy', 'forum_id'],
            ])
            ->apiParams([
                'q'         => ':q',
                'sort'      => ':sort',
                'sort_type' => ':sort_type',
                'forum_id'  => ':id',
                'owner_id'  => ':owner_id',
                'user_id'   => ':user_id',
                'when'      => ':when',
                'view'      => ':view',
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
            ->apiUrl('core/mobile/form/forum.forum_thread.store')
            ->apiParams(['owner_id' => ':id']);

        $this->add('editItem')
            ->apiUrl('core/mobile/form/forum.forum_thread.update/:id');

        $this->add('editFeedItem')
            ->apiUrl('core/mobile/form/forum.forum_thread.update/:id');

        $this->add('moveItem')
            ->apiUrl('core/mobile/form/forum.forum_thread.move/:id');

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
                    'message' => __p('forum::phrase.are_you_sure_you_want_to_delete_this_thread_permanently'),
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
            ->apiUrl('core/mobile/form/forum.forum_thread.copy/:id');

        $this->add('searchItem')
            ->apiUrl('forum')
            ->apiParams([
                'q'         => ':q',
                'sort'      => ':sort',
                'when'      => ':when',
                'forum_id'  => ':forum_id',
                'item_type' => ForumThread::ENTITY_TYPE,
            ])
            ->placeholder(__p('forum::web.search_discussions'));

        $this->add('updateLastRead')
            ->apiUrl('forum-thread/last-read/:id')
            ->asPatch()
            ->apiParams(['post_id' => ':id']);

        $this->add('mergeItem')
            ->apiUrl('core/mobile/form/forum.forum_thread.merge/:id');

        $this->add('viewMyThread')
            ->apiUrl('forum-thread')
            ->apiParams([
                'view' => Browse::VIEW_MY,
            ]);

        $this->add('viewSubscribedThread')
            ->apiUrl('forum-thread')
            ->apiParams([
                'view' => ThreadViewScope::VIEW_SUBSCRIBED,
            ]);

        $this->add('viewHistoryThread')
            ->apiUrl('forum-thread')
            ->apiParams([
                'view' => ThreadViewScope::VIEW_HISTORY,
            ]);

        $this->add('viewPendingThread')
            ->apiUrl('forum-thread')
            ->apiParams([
                'view' => Browse::VIEW_PENDING,
            ]);

        $this->add('viewWikiThread')
            ->apiUrl('forum-thread')
            ->apiParams([
                'view' => ThreadViewScope::VIEW_WIKI,
            ]);

        $this->add('searchGlobalThread')
            ->apiUrl('search')
            ->apiParams([
                'view'                        => 'forum_thread',
                'q'                           => ':q',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ]);

        $this->add('viewMyPendingThread')
            ->apiUrl('forum-thread')
            ->apiParams([
                'view' => Browse::VIEW_MY_PENDING,
            ]);

        $this->add('viewOnOwner')
            ->apiUrl('forum-thread')
            ->apiParams([
                'user_id' => ':id',
            ]);

        $this->add('searchInOwner')
            ->apiUrl('forum-thread')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('forum::web.search_discussions'));
    }
}
