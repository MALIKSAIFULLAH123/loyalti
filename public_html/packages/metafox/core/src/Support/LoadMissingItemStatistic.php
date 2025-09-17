<?php

namespace MetaFox\Core\Support;

use Illuminate\Database\Query\Builder;
use MetaFox\Core\Models\ItemStatistics;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingItemStatistic
{
    public function reject()
    {
        return true;
    }

    /**
     * @param  Reducer $reducer
     * @return void
     * @see \MetaFox\Platform\Traits\Helpers\CommentTrait::getItemStatistic
     */
    public function after(Reducer $reducer)
    {
        $items = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Content && $x->entityType() !== 'feed')
            ->map(fn ($x) => [$x->entityType(), $x->entityId()]);

        if ($items->isEmpty()) {
            return;
        }

        $key = fn ($type, $id) => sprintf('statistic::(%s,%s)', $type, $id);

        $data = $items->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x[0], $x[1])] = null;

            return $carry;
        }, []);

        // combile query to single one
        /** @var Builder $query */
        $query = $items->map(function ($item) {
            return ItemStatistics::query()->where([
                'item_type' => $item[0],
                'item_id'   => $item[1],
            ])->limit(1);
        })->reduce(function ($carry, $item) {
            return $carry ? $carry->union($item) : $item;
        });

        return $query->get()
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->item_type, $x->item_id)] = $x;

                return $carry;
            }, $data);
    }
}
