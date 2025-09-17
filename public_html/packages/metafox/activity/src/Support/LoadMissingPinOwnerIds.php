<?php

namespace MetaFox\Activity\Support;

use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Pin;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingPinOwnerIds
{
    public function handle(Reducer $reducer)
    {
        /** @link \MetaFox\Activity\Repositories\Eloquent\PinRepository::getPinOwnerIds */
        $feeds = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Feed)
            ->map(fn ($x) => $x->id);

        if ($feeds->isEmpty()) {
            return null;
        }

        $key = fn ($feedId) => sprintf('feed:pinedOwnerIds(feed:%s)', $feedId);

        $data = $feeds->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = [];

            return $carry;
        }, []);

        return Pin::query()
            ->select(['owner_id', 'feed_id'])
            ->whereIn('feed_id', $feeds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->feed_id)][] = $x->owner_id;

                return $carry;
            }, $data);
    }
}
