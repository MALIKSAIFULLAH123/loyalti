<?php

namespace MetaFox\Event\Support;

use MetaFox\Event\Models\Invite;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingInvite
{
    public function reject($listing)
    {
        return !$listing;
    }

    /**
     * @param  \MetaFox\User\Models\User $context
     * @param  Reducer                   $reducer
     * @return array|null
     */
    public function handle($context, $reducer): ?array
    {
        $userId = $context?->userId();

        if (!$userId) {
            return null;
        }

        $events = $reducer->entities()
            ->filter(fn ($x) => $x->entityType() === 'event')
            ->map(fn ($x) => $x->id);

        if ($events->isEmpty()) {
            return null;
        }

        $key = fn ($id) => sprintf('event::pendingInvite(user:%s,event:%s)', $userId, $id);

        $data = $events->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = null;

            return $carry;
        }, []);

        return Invite::query()
            ->where('owner_id', $userId)
            ->where('status_id', Invite::STATUS_PENDING)
            ->whereIn('event_id', $events)
            ->get()
            ->reduce(function ($carry, $item) use ($key) {
                $carry[$key($item->id)] = $item;

                return $carry;
            }, $data);
    }
}
