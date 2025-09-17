<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Support\Browse\Scopes\PostViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;

/**
 *--------------------------------------------------------------------------
 * ForumPost Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class ForumPostWebSetting.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('forum-post')
            ->apiRules([
                'view'      => ['includes', 'view', PostViewScope::getAllowView()],
                'sort'      => ['includes', 'sort', SortScope::getAllowSort()],
                'sort_type' => ['includes', 'sort_type', SortScope::getAllowSortType()],
                'when'      => ['includes', 'when', WhenScope::getAllowWhen()],
                'thread_id' => ['truthy', 'thread_id'],
                'post_id'   => ['truthy', 'post_id'],
            ]);

        $this->add('homePage')
            ->pageUrl('forum');

        $this->add('addItem')
            ->apiUrl('forum-post/form')
            ->apiParams(['thread_id' => ':id']);

        $this->add('editItem')
            ->apiUrl('forum-post/form/:id');

        $this->add('deleteItem')
            ->apiUrl('forum-post/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('forum::phrase.are_you_sure_you_want_to_delete_this_post_permanently'),
                ]
            );

        $this->add('approveItem')
            ->apiUrl('forum-post/approve/:id')
            ->asPatch();

        $this->add('quoteItem')
            ->apiUrl('forum-post/quote/form/:id');

        $this->add('searchItem')
            ->pageUrl('forum/search')
            ->pageParams(['item_type' => ForumPost::ENTITY_TYPE])
            ->placeholder(__p('forum::web.search_discussions'));
    }
}
