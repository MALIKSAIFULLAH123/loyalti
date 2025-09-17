<?php

namespace MetaFox\Music\Contracts;

/**
 * Interface HasMediaFile.
 *
 * Determine a resource has image field.
 */
interface HasMediaFile
{
    public function getMediaFileId(): ?string;

    /**
     * @return string|null
     * @deprecated
     */
    public function getLinkMediaFileAttribute(): ?string;
}
