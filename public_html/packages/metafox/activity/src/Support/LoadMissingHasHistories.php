<?php

namespace MetaFox\Activity\Support;

use MetaFox\Activity\Models\ActivityHistory;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingHasHistories
{
    public function handle(Reducer $reducer)
    {
        $feeds = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Feed)
            ->map(fn ($x) => $x->id);

        if ($feeds->isEmpty()) {
            return;
        }

        /** @see \MetaFox\Activity\Policies\FeedPolicy::viewHistory $key */
        $key = fn ($id) => sprintf('feed::hasHistories(feed:%s)', $id);

        $data = $feeds->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = false;

            return $carry;
        }, []);

        return ActivityHistory::query()
            ->whereIn('feed_id', $feeds)
            ->get(['feed_id'])
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->feed_id)] = true;

                return $carry;
            }, $data);
    }
}
