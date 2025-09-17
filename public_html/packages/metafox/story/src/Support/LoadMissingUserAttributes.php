<?php

namespace MetaFox\Story\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\Story\Repositories\StorySetRepositoryInterface;

class LoadMissingUserAttributes
{
    /**
     * @param User    $context
     * @param Reducer $reducer
     *
     * @return void|null
     */
    public function after($context, $reducer)
    {
        $userId = $context?->id;

        $userIds = $reducer->entities()
            ->filter(fn($x) => $x instanceof User && $x->entityType() == 'user')
            ->map(fn($x) => $x->id)
            ->unique();

        if (!$userIds instanceof Collection) {
            return null;
        }

        if ($userIds->isEmpty()) {
            return null;
        }

        /**@var StorySetRepositoryInterface $setRepository */
        $setRepository = resolve(StorySetRepositoryInterface::class);
        $key           = fn($id) => sprintf('story::userAttributes(user:%s,user:%s)', $userId, $id);

        $data = $userIds->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x)] = [
                'has_new_story'  => false,
                'has_live_story' => false,
            ];

            return $carry;
        }, []);

        $table     = $setRepository->getModel()->getTable();
        $hasNewRaw = DB::raw("CASE WHEN v.total_stories > v.total_view THEN true ELSE false END as has_new");

        if (DB::getDriverName() === 'mysql') {
            $hasLiveRaw = DB::raw("CASE WHEN $table.id in (select set_id from stories where JSON_VALUE(extra, '$.is_streaming') = 'true') THEN true ELSE false END as has_live");
        } else {
            $hasLiveRaw = DB::raw("CASE WHEN $table.id in (select set_id from stories where extra->>'is_streaming'= 'true') THEN true ELSE false END as has_live");
        }

        /** @link \MetaFox\Story\Listeners\UserAttributesExtraListener::handle */
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $userIds->map(function ($x) use ($reducer, $context, $setRepository, $hasNewRaw, $hasLiveRaw) {
            $query = $setRepository->getStorySets($context, [
                'user_id'      => $x,
                'ignore_muted' => false,
            ]);

            $query->addSelect($hasNewRaw, $hasLiveRaw);

            return $query;
        })->reduce(function ($carry, $x) use ($setRepository, $hasNewRaw, $hasLiveRaw) {
            if (!$carry) {
                return $x;
            }

            $x->addSelect($hasNewRaw, $hasLiveRaw);
            $carry->union($x);

            return $carry;
        });

        if (!$query) {
            return $data;
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return $data;
        }

        return $rows->reduce(function ($carry, $x) use ($key) {
            Arr::set($carry[$key($x->userId())], 'has_new_story', $x->has_new);
            Arr::set($carry[$key($x->userId())], 'has_live_story', $x->has_live);
            Arr::set($carry[$key($x->userId())], 'can_view_story', true);
            return $carry;
        }, $data);
    }
}
