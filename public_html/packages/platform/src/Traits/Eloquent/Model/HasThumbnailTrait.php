<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Platform\Contracts\HasThumbnail;

/**
 * Trait HasThumbnailTrait.
 *
 * @mixin HasThumbnail
 */
trait HasThumbnailTrait
{
    public function getSizes(): array
    {
        return ResizeImage::SIZE;
    }

    /**
     * @return string|null
     */
    public function getImageAttribute(): ?string
    {
        $thumbnail = $this->getThumbnail();

        if (null === $thumbnail) {
            return null;
        }

        return app('storage')->getUrl($thumbnail);
    }

    public function getImagesAttribute(): ?array
    {
        return app('storage')->getUrls($this->getThumbnail());
    }
}
