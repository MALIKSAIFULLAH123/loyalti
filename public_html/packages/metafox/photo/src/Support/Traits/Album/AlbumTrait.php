<?php

namespace MetaFox\Photo\Support\Traits\Album;

use Illuminate\Support\Collection;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Support\Facades\Album as AlbumFacade;
use MetaFox\Platform\Contracts\Media;

trait AlbumTrait
{
    protected function getAlbumItems(): array|Collection
    {
        $items = AlbumFacade::getMediaItems($this->resource);

        if (empty($items) || $items->isEmpty()) {
            return [];
        }

        return $items->map(function (AlbumItem $item) {
            return ResourceGate::asEmbed($item->detail);
        });
    }

    protected function increasePhotoAlbumTotal(Media $item): void
    {
        if (!$this->shouldIncreasePhotoAlbumTotal($item)) {
            return;
        }

        $album = $item->album;

        $album->update([
            'total_item' => $album->approvedItems()->count(),
        ]);
    }

    protected function shouldIncreasePhotoAlbumTotal(Media $item): bool
    {
        if (!$item->isApproved()) {
            return false;
        }

        return $item->album instanceof Album;
    }
}
