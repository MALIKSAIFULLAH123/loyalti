<?php

namespace MetaFox\Activity\Support;

use Illuminate\Database\Query\Builder;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Stream;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingReviewTagStreams
{
    /**
     * @param  User            $context
     * @param  Reducer         $reducer
     * @return mixed|void|null
     */
    public function handle($context, Reducer $reducer)
    {
        $userId = $context?->id;
        $feeds  = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Feed)
            ->map(fn ($x) => $x->id);

        if ($feeds->isEmpty()) {
            return;
        }

        /** @see \MetaFox\Activity\Policies\FeedPolicy::reviewTagStreams */
        $key = fn ($id) => sprintf('feed::hasReviewTagStreams(user:%s,feed:%s)', $userId, $id);

        $data = $feeds->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = false;

            return $carry;
        }, []);

        /** @var Builder $query */
        $query = $feeds->map(fn ($x) => Stream::query()
            ->select('feed_id')
            ->where('owner_id', $userId)
            ->where('feed_id', $x)
            ->where('status', Stream::STATUS_ALLOW)
            ->limit(1))
            ->reduce(fn ($carry, $x) => $carry ? $carry->union($x) : $x);

        return $query->get()
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->feed_id)] = true;

                return $carry;
            }, $data);
    }
}
