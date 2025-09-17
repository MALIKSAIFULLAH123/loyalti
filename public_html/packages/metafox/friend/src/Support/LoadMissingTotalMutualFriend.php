<?php

namespace MetaFox\Friend\Support;

use Illuminate\Database\Query\JoinClause;
use MetaFox\Friend\Models\Friend as ModelFriend;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingTotalMutualFriend
{
    public function after($context, Reducer $reducer)
    {
        $userId = $context->id;

        $users = $reducer->entities()
            ->filter(fn ($x) => $x->entityType() === 'user' && $x->id != $userId)
            ->map(fn ($x) => $x->id)
            ->unique();

        if ($users->count() < 2) {
            return null;
        }

        $key = fn ($id) => sprintf('friend::countMutualFriends(user:%s,owner:%s)', $userId, $id);

        $data = $users->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x)] = 0;

            return $carry;
        }, []);

        return ModelFriend::query()
            ->selectRaw('f2.user_id, count(*) as aggregate')
            ->from('friends as f')
            ->join('friends as f2', function (JoinClause $join) use ($users, $userId) {
                $join->on('f.owner_id', '=', 'f2.owner_id');
                $join->where('f.user_id', '=', $userId);
                $join->whereIn('f2.user_id', $users->all());
            })
            ->groupBy('f2.user_id')
            ->get()
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->user_id)] = $x->aggregate;

                return $carry;
            }, $data);
    }
}
