<?php

namespace MetaFox\Comment\Support;

use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;

/**
 * @deprecated 4.5.2
 */
class LoadMissingTotalPendingComment
{
    /**
     * @param  User       $context
     * @param  Reducer    $reducer
     * @return array|null
     */
    public function after($context, Reducer $reducer): ?array
    {
        $userId = $context?->userId();

        $items = $reducer->entities()
            ->filter(fn ($x) => $x instanceof HasTotalComment && $x->entityType() !== 'feed')
            ->map(fn ($x) => [$x->entityType(), $x->entityId()]);

        if (!$userId || $items->isEmpty()) {
            return null;
        }

        $key = fn ($type, $id) => sprintf('comment::totalPendingComment(user:%s,%s:%s)', $userId, $type, $id);

        $map = $items->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x[0], $x[1])] = 0;

            return $carry;
        }, []);

        $ors = $items->map(function ($x) use ($userId) {
            if ('comment' == $x[0]) {
                return [
                    'parent_id' => $x[1],
                ];
            }

            return [
                'item_id'   => $x[1],
                'item_type' => $x[0],
            ];
        });

        $query = Comment::query()
            ->selectRaw('count(*) as aggregate, item_type, item_id')
            ->newQuery()
            ->where(['user_id' => $userId, 'is_approved' => 0])
            ->where(function ($builder) use ($ors) {
                $ors->each(fn ($x) => $builder->orWhere($x));
            })
            ->groupBy(['item_type', 'item_id']);

        return $query->get()
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->item_type, $x->item_id)] = $x->aggregate;

                return $carry;
            }, $map);
    }
}
