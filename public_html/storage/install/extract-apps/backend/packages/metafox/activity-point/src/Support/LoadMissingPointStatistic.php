<?php

namespace MetaFox\ActivityPoint\Support;

use MetaFox\ActivityPoint\Models\PointStatistic;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingPointStatistic
{
    /**
     * @param  Reducer    $reducer
     * @return array|null
     */
    public function handle($reducer)
    {
        $key = fn ($id) => sprintf('apt::pointStatistic(user:%s)', $id);

        $ids = $reducer->entities()
            ->filter(fn ($x) => $x->entityType() === 'user')
            ->map(fn ($x) => $x->id)
            ->unique();

        if ($ids->isEmpty()) {
            return null;
        }

        $data = $ids->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = null;

            return $carry;
        }, []);

        return PointStatistic::query()
            ->whereIn('id', $ids)
            ->get()
            ->reduce(function ($carry, $item) use ($key) {
                $carry[$key($item->id)] = $item;

                return $carry;
            }, $data);
    }
}
