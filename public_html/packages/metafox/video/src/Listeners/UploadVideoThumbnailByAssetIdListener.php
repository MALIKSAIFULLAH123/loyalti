<?php

namespace MetaFox\Video\Listeners;

use Illuminate\Http\UploadedFile;
use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\VideoRepositoryInterface;

class UploadVideoThumbnailByAssetIdListener
{
    /**
     * @param  string $assetId
     * @return void
     */
    public function handle(UploadedFile $file, ?string $assetId = null): ?StorageFile
    {
        $video = resolve(VideoRepositoryInterface::class)->getVideoByAssetId($assetId);

        if (!$video instanceof Video) {
            return null;
        }

        return upload()
            ->setStorage('photo')
            ->setPath('video')
            ->setThumbSizes(ResizeImage::SIZE)
            ->setItemType('photo')
            ->setUser($video->user)
            ->storeFile($file);
    }
}
