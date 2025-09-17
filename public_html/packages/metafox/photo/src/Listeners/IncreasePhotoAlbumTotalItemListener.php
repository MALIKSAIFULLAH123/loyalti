<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Support\Traits\Album\AlbumTrait;
use MetaFox\Platform\Contracts\Media;

class IncreasePhotoAlbumTotalItemListener
{
    use AlbumTrait;

    public function handle(?Media $item): void
    {
        if (!$item instanceof Media) {
            return;
        }

        $this->increasePhotoAlbumTotal($item);
    }
}
