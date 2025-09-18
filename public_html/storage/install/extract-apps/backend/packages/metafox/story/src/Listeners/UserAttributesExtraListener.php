<?php

namespace MetaFox\Story\Listeners;

use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Story\Policies\StoryPolicy;
use MetaFox\Story\Repositories\StorySetRepositoryInterface;
use MetaFox\User\Models\User as UserModels;

class UserAttributesExtraListener
{
    public function __construct(protected StorySetRepositoryInterface $repository)
    {
    }

    /**
     * @param User      $context
     * @param User|null $user
     *
     * @return array<string, mixed>
     */
    public function handle(User $context, ?User $user = null): array
    {
        $extra = [
            'can_view_story' => false,
            'has_new_story'  => false,
            'has_live_story' => false,
        ];

        if (!$user instanceof UserModels) {
            return [];
        }

        if (!policy_check(StoryPolicy::class, 'viewAny', $context)) {
            return $extra;
        }

        /* @link \MetaFox\Story\Support\LoadMissingUserAttributes::after */
        $attributes = LoadReduce::get(
            sprintf('story::userAttributes(user:%s,user:%s)', $context->entityId(), $user->entityId()),
            fn() => $this->getAttributes($context, $user)
        );

        if (empty($attributes)) {
            return $extra;
        }

        return array_merge($extra, $attributes);
    }

    protected function getAttributes(User $context, ?User $user): array
    {
        $table     = $this->repository->getModel()->getTable();
        $hasNewRaw = DB::raw("CASE WHEN v.total_stories > v.total_view THEN true ELSE false END as has_new");

        if (DB::getDriverName() === 'mysql') {
            $hasLiveRaw = DB::raw("CASE WHEN $table.id in (select set_id from stories where JSON_VALUE(extra, '$.is_streaming') = 'true') THEN true ELSE false END as has_live");
        } else {
            $hasLiveRaw = DB::raw("CASE WHEN $table.id in (select set_id from stories where extra->>'is_streaming'= 'true') THEN true ELSE false END as has_live");
        }

        $query = $this->repository->getStorySets($context, [
            'user_id'      => $user->entityId(),
            'ignore_muted' => false,
        ]);

        $query->addSelect($hasNewRaw, $hasLiveRaw);

        $storySets = $query->get();

        if ($storySets->isEmpty()) {
            return [];
        }

        $storySet = $storySets->first();

        return [
            'has_new_story'  => $storySet->has_new,
            'has_live_story' => $storySet->has_live,
            'can_view_story' => true,
        ];
    }
}
