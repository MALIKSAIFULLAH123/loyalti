<?php

namespace MetaFox\Comment\Support;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentHide;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingRelatedComments
{
    /**
     * @param \MetaFox\Platform\LoadReduce\Reducer $reducer
     * @return void
     */
    public function before($reducer, $context)
    {
        $contents = $reducer->entities()
            ->filter(fn($x) => $x instanceof HasTotalComment && $x->entityType() !== 'feed')
            ->map(fn($x) => $x->reactItem());

        if ($contents->isEmpty()) {
            return null;
        }

        $userId = $context?->id;

        $key = fn($type, $id) => sprintf('comment::relatedCommentsByType(user:%s,%s:%s)', $userId, $type, $id);

        $data = $contents->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x->entityType(), $x->entityId())] = new Collection();

            return $carry;
        }, []);

        /**
         * @var CommentRepositoryInterface $service
         */
        $service = resolve(CommentRepositoryInterface::class);

        $attributes = [];

        /** @link \MetaFox\Comment\Listeners\RelatedCommentsListener::handle */
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $contents->map(function ($x) use ($reducer, $context, $service, $attributes) {
            return $service->getRelatedCommentsByTypeQuery($context, $x->entityType(), $x->entityId(), $attributes, true);
        })->reduce(fn($carry, $x) => $carry ? $carry->union($x) : $x);

        if (!$query) {
            return $data;
        }

        $query = $service->getReducerSortingBuilder($query, $context, $attributes);

        // Do more estimate with?

        $rows = $query->with(['tagData'])->get();

        $rows->each(function ($cmt) use ($reducer) {
            $reducer->addEntity($cmt);
        });

        return $rows->reduce(function ($carry, $x) use ($key, $context) {
            $c = $key($x->item_type, $x->item_id);

            if (array_key_exists($c, $carry)) {
                $carry[$c]->add($x);
            }

            if (!Helper::isShowReply()) {
                return $carry;
            }

            $limitReplies = Settings::get('comment.prefetch_replies_on_feed');

            $limitScope = new LimitScope();

            $limitScope->setLimit($limitReplies);

            $x->loadMissing([
                'children' => function (HasMany $q) use ($limitScope, $context) {
                    $q->orderBy('created_at', Helper::getDefaultReplySortType())
                        ->addScope($limitScope);
                    $q->addScope(new PendingScope($context));
                },
            ]);

            return $carry;
        }, $data);
    }

    public function terminate(User $context, Reducer $reducer)
    {
        $userId = $context->id;

        $comments = $reducer->entities()
            ->filter(fn($x) => $x instanceof Comment)
            ->map(fn($x) => $x->id);

        $key  = fn($id) => sprintf('comment::isHidden(user:%s,comment:%s)', $userId, $id);
        $data = $comments->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x)] = false;

            return $carry;
        }, []);

        return CommentHide::query()
            ->where(['user_id' => $userId, 'is_hidden' => 1])
            ->whereIn('item_id', $comments->all())
            ->get(['item_id'])
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->item_id)] = true;

                return $carry;
            }, $data);
    }
}
