<?php

namespace MetaFox\Saved\Support;

use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\Saved\Models\Saved;

class LoadMissingIsSaved
{
    /**
     * @param  User       $context
     * @param  Reducer    $reducer
     * @return array|null
     */
    public function after($context, $reducer): ?array
    {
        $userId = $context?->userId();
        $items  = $reducer->entities()
            ->filter(fn ($x) => $x instanceof HasSavedItem && $x->entityType() !== 'feed')
            ->map(fn ($x) => [$x->entityType(), $x->entityId()]);

        if ($items->isEmpty()) {
            return null;
        }

        $key = fn ($type, $id) => sprintf('saved::exists(user:%s,%s:%s)', $userId, $type, $id);

        $data = $items->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x[0], $x[1])] = false;

            return $carry;
        }, []);

        return Saved::query()
            ->where(function ($builder) use ($items) {
                $items->each(fn ($x) => $builder->orWhere(fn ($c) => $c->where([
                    'item_id'   => $x[1],
                    'item_type' => $x[0],
                ])));
            })
            ->where(['user_id' => $userId])
            ->get()
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->item_type, $x->item_id)] = true;

                return $carry;
            }, $data);
    }
}
