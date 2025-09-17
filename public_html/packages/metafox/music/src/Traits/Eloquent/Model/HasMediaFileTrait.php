<?php

namespace MetaFox\Music\Traits\Eloquent\Model;

use  MetaFox\Music\Contracts\HasMediaFile;

/**
 * Trait HasMediaFileTrait.
 *
 * @mixin HasMediaFile
 */
trait HasMediaFileTrait
{
    /**
     * @return string|null
     */
    public function getLinkMediaFileAttribute(): ?string
    {
        $fileId = $this->getMediaFileId();

        if (null === $fileId) {
            return null;
        }

        return app('storage')->getUrl($fileId);
    }
}
