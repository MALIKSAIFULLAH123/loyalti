<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasCover.
 *
 * Determine a resource has cover.
 *
 * @property array<int|string, mixed> $cover
 */
interface HasCover
{
    /**
     * @return array<int>
     */
    public function getCoverSizes(): array;

    /**
     * @return string|null
     */
    public function getCoverType(): ?string;

    /**
     * @return array<string, mixed>|null
     */
    public function getCoversAttribute(): ?array;

    /**
     * @return string|null
     */
    public function getCoverPhotoPosition(): ?string;
}
