<?php

namespace MetaFox\Like\Support;

use MetaFox\Like\Models\Like;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingIsLiked
{
    /**
     * @param  User       $context
     * @param  Reducer    $reducer
     * @return array|null
     * @link \MetaFox\Like\Repositories\Eloquent\LikeRepository::isLiked
     */
    public function after($context, $reducer)
    {
        /** @var $userId */
        $userId = $context?->id;
        $items  = $reducer->entities()
            ->filter(fn ($x) => $x instanceof HasTotalLike && $x->entityType() != 'feed');

        if ($items->isEmpty()) {
            return null;
        }

        $key = fn ($itemType, $itemId) => sprintf('like::isLiked(user:%s,%s:%s)', $userId, $itemType, $itemId);

        $map = [];
        $items->each(function ($item) use ($key, &$map) {
            $map[$key($item->entityType(), $item->entityId())] = false;
        }, []);

        Like::query()
            ->where(['user_id' => $userId])
            ->where(function ($query) use ($items) {
                $items->each(fn ($item) => $query->orWhere(fn ($builder) => $builder->where([
                    'item_id'   => $item->entityId(),
                    'item_type' => $item->entityType(),
                ])));
            })
            ->get()
            ->each(function ($item) use (&$map, $key) {
                $map[$key($item->item_type, $item->item_id)] = true;
            });

        return $map;
    }
}
