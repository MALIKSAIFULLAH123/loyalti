<?php

namespace MetaFox\Group\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingIsPendingInvite
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

        $key = fn ($id) => sprintf('user::isPendingInvited(user:%s,owner:%s)', $userId, $id);

        $data = $pages->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x)] = false;

            return $carry;
        }, []);

        return Invite::query()
            ->where('owner_id', $userId)
            ->where('status_id', Invite::STATUS_PENDING)
            ->where(function (Builder $where) {
                $where->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', Carbon::now()->toDateTimeString());
            })
            ->whereIn('group_id', $pages->all())
            ->get(['group_id'])
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->group_id)] = true;

                return $carry;
            }, $data);
    }
}
