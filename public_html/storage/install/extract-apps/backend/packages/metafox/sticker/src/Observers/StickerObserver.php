<?php

namespace MetaFox\Sticker\Observers;

use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;

/**
 * Class StickerObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class StickerObserver
{
    public function created(Sticker $sticker): void
    {
        $stickerSet = $sticker->stickerSet;
        if (!$stickerSet instanceof StickerSet) {
            return;
        }

        if (method_exists($stickerSet, 'incrementAmount')) {
            $stickerSet->incrementAmount('total_sticker');
        }

        if ($stickerSet->thumbnail_id == 0) {
            resolve(StickerSetRepositoryInterface::class)->updateThumbnail($stickerSet, $sticker->entityId());
        }
    }

    public function updated(Sticker $sticker): void
    {
        if ($sticker->wasChanged(['is_deleted'])) {
            $stickerSet = $sticker->stickerSet;

            if (!$stickerSet instanceof StickerSet) {
                return;
            }

            if (method_exists($stickerSet, 'decrementAmount')) {
                $stickerSet->decrementAmount('total_sticker');
            }

            if ($stickerSet->thumbnail_id == $sticker->entityId()) {
                resolve(StickerSetRepositoryInterface::class)->updateThumbnail($stickerSet);
            }
        }
    }
}
