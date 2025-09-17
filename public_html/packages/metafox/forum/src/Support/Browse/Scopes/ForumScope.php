<?php

namespace MetaFox\Forum\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class ForumScope extends BaseScope
{
    /**
     * @var int
     */
    protected $forumId;

    /**
     * @var string
     */
    protected $itemType;

    public function __construct(int $forumId, string $itemType)
    {
        $this->forumId = $forumId;

        $this->itemType = $itemType;
    }

    public function apply(Builder $builder, Model $model)
    {
        $forumId  = $this->forumId;
        $itemType = $this->itemType;
        $forumIds = resolve(ForumRepositoryInterface::class)->getDescendantIds($forumId);

        if (!count($forumIds)) {
            $forumIds = [0];
        }

        switch ($itemType) {
            case ForumThread::ENTITY_TYPE:
                $builder->where([
                    'forum_threads.is_wiki' => 0,
                ])
                ->whereIn('forum_threads.forum_id', $forumIds);
                break;
            case ForumPost::ENTITY_TYPE:
                $builder->join('forum_threads as ft', function (JoinClause $clause) use ($forumIds) {
                    $clause->on('ft.id', '=', 'forum_posts.thread_id')
                        ->where([
                            'ft.is_wiki' => 0,
                        ])
                        ->whereIn('ft.forum_id', $forumIds);
                });
                break;
        }
    }
}
