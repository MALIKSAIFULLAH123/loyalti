<?php

namespace MetaFox\Sticker\Observers;

use MetaFox\Sticker\Jobs\DeleteStickerRecentJob;
use MetaFox\Sticker\Models\StickerUserValue;

/**
 * Class StickerUserValueObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class StickerUserValueObserver
{
    public function deleted(StickerUserValue $stickerUserValue): void
    {
        DeleteStickerRecentJob::dispatch($stickerUserValue->set_id);
    }
}
