<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Comment\Http\Resources\v1\Comment;

use MetaFox\Comment\Support\Helper;
use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Comment Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('deleteItem')
            ->apiUrl('comment/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('comment::phrase.delete_confirm'),
                ]
            );

        $this->add('addItem')
            ->apiUrl('comment')
            ->asPost();

        $this->add('editItem')
            ->apiUrl('comment/:id')
            ->asPut();

        $this->add('viewAll')
            ->apiUrl('comment')
            ->asGet()
            ->apiParams([
                'item_type' => ':comment_type_id',
                'item_id'   => ':comment_item_id',
            ]);

        $this->add('getUsersCommentByItem')
            ->apiUrl('comment-lists')
            ->asGet()
            ->apiParams([
                'item_id'   => ':item_id',
                'item_type' => ':item_type',
                'limit'     => ':limit',
            ]);

        $this->add('hideItem')
            ->apiUrl('comment/hide')
            ->asPost()
            ->apiParams([
                'comment_id' => ':id',
                'is_hidden'  => 1,
            ]);

        $this->add('unhideItem')
            ->apiUrl('comment/hide')
            ->asPost()
            ->apiParams([
                'comment_id' => ':id',
                'is_hidden'  => 0,
            ]);

        $this->add('hideGlobalItem')
            ->apiUrl('comment/hide')
            ->asPost()
            ->apiParams([
                'comment_id' => ':id',
                'is_hidden'  => 1,
                'is_global'  => 1,
            ]);

        $this->add('unhideGlobalItem')
            ->apiUrl('comment/hide')
            ->asPost()
            ->apiParams([
                'comment_id' => ':id',
                'is_hidden'  => 0,
                'is_global'  => 1,
            ]);

        $this->add('viewCommentHistories')
            ->apiUrl('comment/history-edit/:id')
            ->asGet();

        $this->add('removePreviewItem')
            ->apiUrl('comment/:id/remove-preview')
            ->asPatch();

        $this->add('approveItem')
            ->apiUrl('comment/approve/:id')
            ->asPatch();

        $this->add('viewReplies')
            ->apiUrl('comment')
            ->apiParams([
                'item_type' => ':comment_type_id',
                'item_id' => ':comment_item_id',
                'parent_id' => ':parent_id',
                'last_id' => ':last_id',
                'sort_type' => Helper::getDefaultReplySortType(),
            ]);

        $this->add('viewReplyDetail')
            ->apiUrl('comment/reply/:id');

        $this->add('translateItem')
            ->apiUrl('comment/translate')
            ->apiParams([
                'id'     => ':id',
                'target' => ':target',
            ])
            ->asGet();
    }
}
