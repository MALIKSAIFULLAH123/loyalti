<?php

namespace MetaFox\Friend\Support;

use MetaFox\Friend\Models\Friend as FriendModel;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingFriend
{
    /**
     * @param             $context
     * @param  Reducer    $reducer
     * @return mixed|null
     */
    public function after($context, $reducer)
    {
        $userId = $context?->userId();
        if (!$userId) {
            return null;
        }

        $users = $reducer->entities()
            ->filter(fn ($x) => $x->entityType() === 'user' && $x->id != $userId)
            ->map(fn ($x) => $x->id)
            ->unique();

        if ($users->isEmpty()) {
            return null;
        }

        $key = fn ($userId, $ownerId) => sprintf('friend::exists(user:%s,user:%s)', $userId, $ownerId);

        $data = $users->reduce(function ($carry, $item) use ($userId, $key) {
            $carry[$key($userId, $item)] = false;
            $carry[$key($item, $userId)] = false;

            return $carry;
        }, []);

        return FriendModel::query()
            ->where(function ($query) use ($userId, $users) {
                $query->where('user_id', $userId)
                    ->whereIn('owner_id', $users->all());
            })
            ->orWhere(function ($query) use ($userId, $users) {
                $query->where('owner_id', $userId)
                    ->whereIn('user_id', $users->all());
            })->get()
            ->reduce(function ($carry, $item) use ($key) {
                $carry[$key($item->user_id, $item->owner_id)] = true;
                $carry[$key($item->owner_id, $item->user_id)] = true;

                return $carry;
            }, $data);
    }
}
