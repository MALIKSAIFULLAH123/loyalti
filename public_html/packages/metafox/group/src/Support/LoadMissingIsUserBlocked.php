<?php

namespace MetaFox\Group\Support;

use MetaFox\Group\Models\Block;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingIsUserBlocked
{
    public function after($context, Reducer $reducer)
    {
        $userId = $context->id;
        $pages  = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Group)
            ->map(fn ($x) => $x->id);

        if ($pages->isEmpty()) {
            return;
        }

        $key = fn ($id) => sprintf('user::blocked(user:%s,owner:%s)', $userId, $id);

        $data = $pages->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x)] = false;

            return $carry;
        }, []);

        return Block::query()
            ->where('user_id', $userId)
            ->whereIn('group_id', $pages->all())
            ->get(['group_id'])
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->group_id)] = true;

                return $carry;
            }, $data);
    }
}
