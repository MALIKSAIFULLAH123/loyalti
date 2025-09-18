<?php

namespace MetaFox\Story\Listeners;

use MetaFox\Story\Repositories\StoryRepositoryInterface;

class DeleteVideoByAssetIdListener
{
    public function __construct(protected StoryRepositoryInterface $repository) { }

    /**
     * @param string|null $assetId
     * @return void
     */
    public function handle(?string $assetId = null): void
    {
        if ($assetId == null) {
            return;
        }
        
        $this->repository->deleteVideoByAssetId($assetId);
    }
}
