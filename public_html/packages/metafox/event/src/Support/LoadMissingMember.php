<?php

namespace MetaFox\Event\Support;

use MetaFox\Event\Models\Member;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingMember
{
    /**
     * @param  User       $context
     * @param  Reducer    $reducer
     * @return array|null
     */
    public function after($context, $reducer)
    {
        $userId = $context?->userId();
        $events = $reducer->entities()
            ->filter(fn ($x) => $x->entityType() === 'event')
            ->map(fn ($x) => $x->id);

        if (!$userId || $events->isEmpty()) {
            return null;
        }

        $key = fn ($event) => sprintf('event::member(user:%s,event:%s)', $userId, $event);

        $data = $events->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = null;

            return $carry;
        }, []);

        return Member::query()
            ->where('user_id', $userId)
            ->whereIn('event_id', $events)
            ->get()
            ->reduce(function ($carry, $item) use ($key) {
                $carry[$key($item->event_id)] = $item;

                return $carry;
            }, $data);
    }
}
