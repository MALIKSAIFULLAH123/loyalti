<?php

namespace MetaFox\User\Support;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\User\Models\UserBlocked;

class LoadMissingIsBlocked
{
    /**
     * @param  User    $context
     * @param  Reducer $reducer
     * @return void
     */
    public function terminate(User $context, Reducer $reducer)
    {
        $userId = $context?->id;

        $items = $reducer->entities()
            ->filter(fn ($x) => $x instanceof User && $x->id !== $userId)
            ->map(fn ($x) => $x->id)
            ->unique();

        if (!$userId || $items->isEmpty()) {
            return null;
        }

        $key = fn ($userId, $ownerId) => sprintf('user::blocked(user:%s,owner:%s)', $userId, $ownerId);

        $data = $items->reduce(function ($carry, $id) use ($userId, $key) {
            $carry[$key($userId, $id)] = false;
            $carry[$key($id, $userId)] = false;

            return $carry;
        }, []);

        return UserBlocked::query()
            ->withoutGlobalScopes()
            ->where('user_id', $userId)
            ->whereIn('owner_id', $items->all())
            ->orWhere(function ($query) use ($userId, $items) {
                $query->where('owner_id', $userId)
                    ->whereIn('user_id', $items->all());
            })
            ->get(['owner_id', 'user_id'])
            ->reduce(function ($carry, $row) use ($key) {
                $carry[$key($row->user_id, $row->owner_id)] = true;

                return $carry;
            }, $data);
    }
}
