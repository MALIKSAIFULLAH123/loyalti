<?php

namespace MetaFox\Group\Support;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Request;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingMemberRequest
{
    public function after($context, Reducer $reducer)
    {
        $userId = $context->id;
        $groups = $reducer->entities()
            ->map(function ($x) {
                if ($x instanceof Group) {
                    return $x->id;
                }

                if ($x instanceof Request) {
                    return $x->group_id;
                }

                return null;
            });

        $groups = $groups->filter();

        if ($groups->isEmpty()) {
            return null;
        }

        $key = fn($id, $statusId) => sprintf('group::getRequestByUserGroupId(user:%s,group:%s,status_id:%s)', $userId, $id, $statusId);

        return Request::query()
            ->where('user_id', $userId)
            ->whereIn('group_id', $groups)
            ->get()
            ->reduce(function ($carry, $x) use ($key, $reducer) {
                $carry[$key($x->group_id, $x->status_id)] = $x;
                $reducer->addEntity($x);

                return $carry;
            }, []);
    }
}
