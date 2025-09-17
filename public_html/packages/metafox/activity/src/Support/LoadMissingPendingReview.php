<?php

namespace MetaFox\Activity\Support;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Stream;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingPendingReview
{
    public function after(Reducer $reducer)
    {
        $feeds = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Feed)
            ->map(fn ($x) => $x->id);

        $key = fn ($id) => sprintf('feed::toPendingPreview(%s,%s)', 'feed', $id);

        $data = [];

        if ($feeds->isEmpty()) {
            return null;
        }

        /** @link \MetaFox\Activity\Models\Feed::toPendingPreview */
        /** @var Builder $query */
        $query = $feeds->map(fn ($x) => Stream::query()->where(['feed_id' => $x])->limit(1))
            ->reduce(fn ($carry, $x) => $carry ? $carry->union($x) : $x);

        return $query->get()
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->feed_id)] = $x;

                return $carry;
            }, $data);
    }
}
