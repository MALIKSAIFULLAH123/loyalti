<?php

namespace MetaFox\Event\Support;

use MetaFox\Event\Models\Invite;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingTotalPendingInvite
{
    /**
     * @param  User       $context
     * @param  Reducer    $reducer
     * @return array|null
     */
    public function handle($context, $reducer)
    {
        $userId = $context->entityId();

        $events = $reducer->entities()
            ->filter(fn ($x) => $x instanceof \MetaFox\Event\Models\Event);

        if ($events->isEmpty()) {
            return null;
        }

        $keys = fn ($id) => sprintf('event::totalPendingInvite(user:%s,event:%s)', $userId, $id);

        $data = $events->map(fn ($x) => $x->id)
            ->reduce(function ($carry, $id) use ($keys) {
                $carry[$keys($id)] = 0;

                return $carry;
            }, []);

        // don't query if there are no total pending invite
        $need = $events->filter(fn ($x) => $x->total_pending_invite > 0)
            ->map(fn ($x) => $x->id);

        if ($need->isEmpty()) {
            return $data;
        }

        return Invite::query()
            ->selectRaw('count(*) as aggregate, event_id')
            ->where('status_id', Invite::STATUS_PENDING)
            ->whereIn('event_id', $need->all())
            ->groupBy('event_id')
            ->get()
            ->reduce(function ($carry, $item) use ($keys) {
                $carry[$keys($item->event_id)] = $item->aggregate;

                return $carry;
            }, $data);
    }
}
