<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Video\Support\Facade\Video;

class DeleteVideoByAssetIdListener
{
    /**
     * @param string|null $assetId
     * @return void
     */
    public function handle(?string $assetId = null): void
    {
        if ($assetId == null) {
            return;
        }

        Video::deleteVideoByAssetId($assetId);
    }
}
