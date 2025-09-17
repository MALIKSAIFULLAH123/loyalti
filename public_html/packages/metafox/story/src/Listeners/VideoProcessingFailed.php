<?php

namespace MetaFox\Story\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Support\StorySupport;

class VideoProcessingFailed
{
    public function __construct(protected StoryRepositoryInterface $repository) {}

    public function handle(array $params): void
    {
        $assetId = Arr::get($params, 'asset_id');
        if ($assetId == null) {
            return;
        }

        $story = $this->repository->getStoryByAssetId($assetId);

        if (!$story instanceof Story) {
            return;
        }

        $story->update([
            'in_process' => StorySupport::STATUS_VIDEO_FAILED,
        ]);

        $this->repository->publishStories($story);
    }
}
