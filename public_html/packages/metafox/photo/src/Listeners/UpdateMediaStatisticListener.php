<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Support\Traits\HandlePhotoGroupItemStatisticTrait;
use MetaFox\Platform\Contracts\Media;

class UpdateMediaStatisticListener
{
    use HandlePhotoGroupItemStatisticTrait;

    public function handle(?Media $item): void
    {
        if (!$item instanceof Media) {
            return;
        }

        $photoGroup = $item->group;

        if (!$photoGroup instanceof PhotoGroup) {
            return;
        }

        $this->resetSingleItemStatInPhotoGroup($photoGroup, $item->entityId());
    }
}
