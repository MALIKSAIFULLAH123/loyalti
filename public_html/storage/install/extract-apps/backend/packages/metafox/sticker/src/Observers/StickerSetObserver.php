<?php

namespace MetaFox\Sticker\Observers;

use MetaFox\Sticker\Jobs\DeleteStickerRecentJob;
use MetaFox\Sticker\Models\StickerSet;

/**
 * Class StickerSetObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class StickerSetObserver
{
    public function updated(StickerSet $stickerSet): void
    {
        if ($stickerSet->wasChanged(['is_deleted'])) {
            $stickerSet->stickers()->update(['is_deleted' => StickerSet::IS_DELETED]);
            DeleteStickerRecentJob::dispatch($stickerSet->entityId());
        }

        if ($stickerSet->wasChanged(['is_active']) && !$stickerSet->is_active) {
            DeleteStickerRecentJob::dispatch($stickerSet->entityId());
        }
    }
}
