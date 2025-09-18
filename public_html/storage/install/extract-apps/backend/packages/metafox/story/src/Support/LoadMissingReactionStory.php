<?php

namespace MetaFox\Story\Support;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\Story\Models\StoryReaction;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Repositories\StoryRepositoryInterface;

class LoadMissingReactionStory
{
    /**
     * @param User    $context
     * @param Reducer $reducer
     * @return void|null
     */
    public function after($context, $reducer)
    {
        $userId    = $context?->id;
        $storySets = $reducer->entities()
            ->filter(fn ($x) => $x->entityType() === 'story_set')
            ->map(fn ($x) => $x);

        if (!$storySets instanceof Collection) {
            return null;
        }

        if ($storySets->isEmpty()) {
            return null;
        }

        /**@var StoryRepositoryInterface $storyRepository */
        $storyRepository = resolve(StoryRepositoryInterface::class);

        $ids = new Collection();
        $storySets->each(function (StorySet $item) use ($context, $storyRepository, &$ids) {
            $query    = $storyRepository->getStories($context, $item);
            $storyIds = $query->pluck('id')->toArray();
            $ids      = $ids->merge($storyIds);
        });

        $key = fn ($id) => sprintf('story::reaction(user:%s,story:%s)', $userId, $id);

        if ($ids->isEmpty()) {
            return null;
        }

        $data = $ids->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = null;

            return $carry;
        }, []);

        return StoryReaction::query()
            ->where('user_id', $userId)
            ->whereIn('story_id', $ids)
            ->get()
            ->reduce(function ($carry, $item) use ($key) {
                $carry[$key($item->story_id)] = $item;
                return $carry;
            }, $data);
    }
}
