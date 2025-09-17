<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use MetaFox\Platform\Contracts\HasCover;

/**
 * Trait HasCoverTrait.
 *
 * @mixin HasCover
 *
 * @property string $cover_file_id
 * @property string $cover_photo_position
 */
trait HasCoverTrait
{
    public function getCoverSizes(): array
    {
        return ['100', '150', '240', '500', '1024'];
    }

    public function getCoverAttribute(): ?string
    {
        return app('storage')->getUrl($this->cover_file_id);
    }

    public function getCoversAttribute(): ?array
    {
        $covers = app('storage')->getUrls($this->cover_file_id);

        return empty($covers) ? null : $covers;
    }

    public function getCoverPhotoPosition(): ?string
    {
        return $this->cover_photo_position;
    }
}
