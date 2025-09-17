<?php

namespace MetaFox\Like\Support;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingMostReactions
{
    /**
     * @param User    $context
     * @param Reducer $reducer
     *
     * @return mixed|null
     */
    public function handle(User $context, Reducer $reducer)
    {
        $userId = $context?->id;

        $limit  = 3;

        $items  = $reducer->entities()
            ->filter(fn($x) => $x instanceof HasTotalLike)
            ->map(fn($x) => $x->reactItem())
            ->map(fn($x) => [$x->entityType(), $x->entityId()]);

        // build map of items.

        if ($items->isEmpty()) {
            return null;
        }

        $key = fn($type, $id) => sprintf('like::mostReactions(user:%s,%s:%s)', $userId, $type, $id);

        $aggregationKey = fn($type, $id) => sprintf('like::getItemReactionAggregation(%s,%s,%s)', $userId, $type, $id);

        $map = $items->reduce(function ($carry, $x) use ($key, $aggregationKey) {
            $carry[$key($x[0], $x[1])] = new Collection();

            $carry[$aggregationKey($x[0], $x[1])] = [];

            return $carry;
        }, []);

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $items->map(function ($x) use ($limit) {
            return LikeAgg::query()
                ->where([
                    'item_type' => $x[0],
                    'item_id'   => $x[1],
                ])
                ->where('total_reaction', '>', 0)
                ->orderBy('total_reaction', 'DESC')
                ->orderBy('updated_at', 'DESC')
                ->limit($limit);
        })->reduce(function ($carry, $x) {
            return $carry ? $carry->union($x) : $x;
        }, null)
            ->orderBy('total_reaction', 'DESC')
            ->orderBy('updated_at', 'DESC');

        $rows = $query->get();

        $rows->loadMissing('reaction');

        $rows->each(function ($x) use (&$map, $key, $aggregationKey) {
            $name = $key($x->item_type, $x->item_id);

            $aggregationName = $aggregationKey($x->item_type, $x->item_id);

            if (array_key_exists($name, $map)) {
                $map[$name]->add($x->reaction);
            }

            if (array_key_exists($aggregationName, $map)) {
                $map[$aggregationName][] = [
                    'id' => $x->reaction_id,
                    'total_reaction' => $x->total_reaction,
                ];
            }
        });

        return $map;
    }
}
