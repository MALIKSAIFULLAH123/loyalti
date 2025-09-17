<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Comment\Observers;

use MetaFox\Comment\Models\CommentHistory;

class CommentHistoryObserver
{
    /**
     * @param CommentHistory $model
     */
    public function created(CommentHistory $model): void
    {
        app('events')->dispatch('hashtag.create_hashtag', [$model->user, $model, $model->content], true);
    }

    /**
     * @param CommentHistory $model
     */
    public function deleted(CommentHistory $model): void
    {
        $model->tagData()->sync([]);
    }
}
