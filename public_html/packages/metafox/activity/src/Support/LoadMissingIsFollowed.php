<?php

namespace MetaFox\Activity\Support;

use MetaFox\Activity\Models\Subscription;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingIsFollowed
{
    /**
     * @param  User      $context
     * @param  Reducer   $reducer
     * @return void|null
     */
    public function after($context, $reducer)
    {
        $userId = $context?->id;

        $key = fn ($id) => sprintf('follow::exists(user:%s,owner:%s)', $userId, $id);

        $ids = $reducer->entities()
            ->filter(fn ($x) => $x instanceof User && $x->id !== $userId)
            ->map(fn ($x) => $x->id);

        if ($ids->isEmpty()) {
            return null;
        }

        $data = $ids->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = false;

            return $carry;
        }, []);

        return Subscription::query()
            ->where([
                'user_id'      => $userId,
                'is_active'    => true,
                'special_type' => null,
            ])
            ->whereIn('owner_id', $ids)
            ->get()
            ->reduce(function ($carry, $item) use ($key) {
                $carry[$key($item->owner_id)] = true;

                return $carry;
            }, $data);
    }
}
