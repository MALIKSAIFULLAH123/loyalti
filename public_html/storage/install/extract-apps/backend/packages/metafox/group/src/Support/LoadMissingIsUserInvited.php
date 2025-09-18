<?php

namespace MetaFox\Group\Support;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingIsUserInvited
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

        $key = fn ($id) => sprintf('user::isInvited(user:%s,owner:%s)', $userId, $id);

        $data = $pages->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x)] = false;

            return $carry;
        }, []);

        return Invite::query()
            ->where('owner_id', $userId)
            ->whereIn('group_id', $pages->all())
            ->get(['group_id'])
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->group_id)] = true;

                return $carry;
            }, $data);
    }
}
