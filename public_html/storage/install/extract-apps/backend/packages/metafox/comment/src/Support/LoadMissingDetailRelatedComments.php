<?php

namespace MetaFox\Comment\Support;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Facades\Settings;

class LoadMissingDetailRelatedComments
{
    /**
     * @param Collection                           $items
     * @param \MetaFox\Platform\LoadReduce\Reducer $reducer
     * @return void
     */
    public function before($items, $reducer, $context)
    {
        $contents = $items->filter(fn($x) => $x instanceof HasTotalComment)
            ->map(fn($x) => $x->reactItem());

        if ($contents->isEmpty()) {
            return null;
        }

        $userId = $context?->id;

        $key = fn($type, $id) => sprintf('comment::relatedComments(user:%s,%s:%s)', $userId, $type, $id);

        $data = $contents->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x->entityType(), $x->entityId())] = new Collection();

            return $carry;
        }, []);

        $service = resolve(CommentRepositoryInterface::class);

        /** @link \MetaFox\Comment\Listeners\RelatedCommentsItemDetailListener::handle */
        /** @var Builder $query */
        $query = $contents->map(fn($x) => $service->getRelatedCommentForItemDetailQuery($context, $x->entityType(), $x->entityId()))
            ->reduce(fn($carry, $x) => $carry ? $carry->union($x) : $x);

        if (!$query) {
            return null;
        }

        $rows = $query->get();
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
}
