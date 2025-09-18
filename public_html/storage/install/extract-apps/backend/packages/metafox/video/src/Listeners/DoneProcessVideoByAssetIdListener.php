<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Video\Models\Video;

class DoneProcessVideoByAssetIdListener
{
    /**
     * @param  string|null          $assetId
     * @param  array<string, mixed> $params
     * @return void
     */
    public function handle(?string $assetId = null, array $params = []): void
    {
        $service = $this->getVideoRepository();
        $video   = $service->getVideoByAssetId($assetId);

        if (!$video instanceof Video) {
            return;
        }

        $service->doneProcessVideo($video->entityId(), $params);
    }

    protected function getVideoRepository(): VideoRepositoryInterface
    {
        return resolve(VideoRepositoryInterface::class);
    }
}
