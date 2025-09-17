<?php

namespace MetaFox\Friend\Support;

use MetaFox\Friend\Models\FriendRequest;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\User\Models\User;

class LoadMissingFriendRequest
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

        $key = fn ($userId, $ownerId) => sprintf('friend::request(user:%s,owner:%s)', $userId, $ownerId);

        $users = $reducer->entities()
            ->filter(fn ($x) => $x instanceof User && $x->id != $userId)
            ->map(fn ($x) => $x->id)
            ->unique();

        if ($users->isEmpty()) {
            return null;
        }

        $data = $users->reduce(function ($carry, $item) use ($userId, $key) {
            $carry[$key($userId, $item)] = null;
            $carry[$key($item, $userId)] = null;

            return $carry;
        }, []);

        return FriendRequest::query()
            ->where(function ($query) use ($userId, $users) {
                $query->where('user_id', $userId)
                    ->whereIn('owner_id', $users);
            })
            ->orWhere(function ($query) use ($userId, $users) {
                $query->where('owner_id', $userId)
                    ->whereIn('user_id', $users);
            })->get()
            ->reduce(function ($carry, $item) use ($key) {
                $carry[$key($item->user_id, $item->owner_id)] = $item;

                return $carry;
            }, $data);
    }
}
