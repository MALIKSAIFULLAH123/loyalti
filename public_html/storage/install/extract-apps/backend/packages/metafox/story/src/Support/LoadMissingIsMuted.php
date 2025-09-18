<?php

namespace MetaFox\Story\Support;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\Story\Models\Mute;

class LoadMissingIsMuted
{
    /**
     * @param User    $context
     * @param Reducer $reducer
     *
     * @return void|null
     */
    public function after($context, $reducer)
    {
        $userId = $context?->id;

        $ids = $reducer->entities()
            ->filter(fn($x) => $x->entityType() === 'user')
            ->map(fn($x) => $x->id);
        if ($ids->isEmpty()) {
            return null;
        }

        $key = fn($id) => sprintf('story_mute::exists(user:%s,owner:%s)', $userId, $id);

        $data = $ids->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = false;

            return $carry;
        }, []);

        return Mute::query()
            ->where('user_id', $userId)
            ->whereIn('owner_id', $ids)
            ->get()
            ->reduce(function ($carry, $item) use ($key, $reducer) {
                $carry[$key($item->owner_id)] = true;

                return $carry;
            }, $data);
    }
}
