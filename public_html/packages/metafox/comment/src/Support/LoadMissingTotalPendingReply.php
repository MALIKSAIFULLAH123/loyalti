<?php

namespace MetaFox\Comment\Support;

use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\LoadReduce\Reducer;

/**
 * @deprecated 4.5.2
 */
class LoadMissingTotalPendingReply
{
    /**
     * @inherhitDoc
     */
    public function handle($context, $items, Reducer $reducer): ?array
    {
        $userId = $context?->userId();
        if (!$userId || $items->isEmpty()) {
            return null;
        }

        $input = $reducer->entities()
            ->filter(fn ($x) => $x instanceof HasTotalComment && !$x instanceof Comment)
            ->map(fn ($x) => [$x->entityType(), $x->entityId()]);

        if ($input->isEmpty()) {
            return null;
        }

        $key = fn ($type, $id) => sprintf('comment::totalPendingReply(user:%s,%s:%s)', $userId, $type, $id);

        /** @link \MetaFox\Platform\Traits\Helpers\CommentTrait::getTotalReplyAttribute */
        $map = $input->reduce(function ($carry, $item) use ($key) {
            $carry[$key($item[0], $item[1])] = 0;

            return $carry;
        }, []);

        $query = Comment::query()
            ->selectRaw('count(*) as aggregate, item_type, item_id')
            ->where([
                'user_id'     => $userId,
                'is_approved' => 0,
                ['parent_id', '<>', 0],
            ])
            ->groupBy(['item_type', 'item_id'])
            ->where(function ($builder) use ($input) {
                $input->each(fn ($x) => $builder->orWhere(fn ($t) => $t->where([
                    'item_type' => $x[0],
                    'item_id'   => $x[1],
                ])));
            });

        return $query->get()
            ->reduce(function ($carry, $item) use ($key) {
                $carry[$key($item->item_type, $item->item_id)] = $item->aggregate;

                return $carry;
            }, $map);
    }
}
