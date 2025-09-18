<?php

namespace MetaFox\Story\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Notifications\StoryDoneProcessingNotification;
use MetaFox\Story\Repositories\StoryRepositoryInterface;

class DoneProcessVideoByAssetIdListener
{
    public function __construct(protected StoryRepositoryInterface $repository) {}

    /**
     * @param string|null          $assetId
     * @param array<string, mixed> $params
     * @return void
     * @throws \Exception
     */
    public function handle(?string $assetId = null, array $params = []): void
    {
        if ($assetId == null) {
            return;
        }

        $module = Arr::get($params, 'module_name');
        if (Story::ENTITY_TYPE !== $module) {
            return;
        }

        $story = $this->repository->getStoryByAssetId($assetId);

        if (!$story instanceof Story) {
            return;
        }

        if ($story->is_ready) {
            $this->repository->publishStories($story);
            return;
        }

        Arr::set($params, 'duration', (int) Arr::get($params, 'duration'));

        $story->fill($params);

        $story->save();

        $this->repository->publishStories($story);

        // Notify creator that their story video is ready
        Notification::send($story->user, new StoryDoneProcessingNotification($story));
    }
}
