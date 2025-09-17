<?php

namespace MetaFox\Activity\Support;

use Illuminate\Database\Query\Builder;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\HasFeed;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\Platform\Contracts\User;

class LoadMissingFeed
{
    public function reject($listing)
    {
        return !$listing;
    }
    /**
     * @param  Reducer $reducer
     * @return void
     */
    public function handle($reducer)
    {
        $items = $reducer->entities()
            ->filter(fn ($x) => $x instanceof HasFeed && !$x instanceof User && !$x instanceof Feed)
            ->map(fn ($x) => [$x->entityType(), $x->entityId()]);

        if ($items->isEmpty()) {
            return null;
        }

        /* @link \MetaFox\Platform\Traits\Eloquent\Model\HasFeed::getActivityFeedAttribute */
        $key = fn ($type, $id) => sprintf('feed::of(%s:%s)', $type, $id);

        $data = $items->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x[0], $x[1])] = null;

            return $carry;
        }, []);

        /** @var Builder $query */
        $query = $items->map(function ($item) {
            return Feed::query()->where([
                'item_id'   => $item[1],
                'item_type' => $item[0],
            ])->limit(1);
        })->reduce(fn ($carry, $x) => $carry ? $carry->union($x) : $x);

        return $query->get()
            ->reduce(function ($carry, $x) use ($key, $reducer) {
                $carry[$key($x->item_type, $x->item_id)] = $x;
                $reducer->addEntity($x);

                return $carry;
            }, $data);
    }
}
