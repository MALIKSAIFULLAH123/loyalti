<?php

namespace MetaFox\Story\Support;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Models\StoryView;
use MetaFox\Story\Repositories\StoryRepositoryInterface;

class LoadMissingHasSeenStory
{
    /**
     * @param User    $context
     * @param Reducer $reducer
     * @return void|null
     */
    public function after($context, $reducer)
    {
        $userId = $context?->id;

        $storySets = $reducer->entities()
            ->filter(fn($x) => $x->entityType() === 'story_set')
            ->map(fn($x) => $x);

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

        if ($ids->isEmpty()) {
            return null;
        }

        $key = fn($id) => sprintf('story_view::exists(user:%s,story:%s)', $userId, $id);

        $data = $ids->reduce(function ($carry, $id) use ($key) {
            $carry[$key($id)] = false;

            return $carry;
        }, []);

        return StoryView::query()
            ->where('user_id', $userId)
            ->whereIn('story_id', $ids)
            ->get()
            ->reduce(function ($carry, $item) use ($key, $reducer) {
                $carry[$key($item->story_id)] = true;

                return $carry;
            }, $data);
    }
}
