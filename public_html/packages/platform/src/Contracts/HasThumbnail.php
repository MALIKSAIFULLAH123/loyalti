<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasThumbnail.
 *
 * Determine a resource has image field.
 *
 * @property string|null $image
 * @property array|null  $images
 */
interface HasThumbnail
{
    public function getThumbnail(): ?string;

    /**
     * @return array<int>
     * @deprecated
     */
    public function getSizes(): array;

    /**
     * @return string|null
     * @deprecated
     */
    public function getImageAttribute(): ?string;

    /**
     * @return array<string, mixed>|null
     */
    public function getImagesAttribute(): ?array;
}
