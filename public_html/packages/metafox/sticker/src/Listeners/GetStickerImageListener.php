<?php

namespace MetaFox\Sticker\Listeners;

use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;

/**
 * Class GetStickerImageListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetStickerImageListener
{
    /**
     * @param int $stickerId
     *
     * @return null|array<string, mixed>
     */
    public function handle(int $stickerId)
    {
        $sticker = resolve(StickerSetRepositoryInterface::class)->getSticker($stickerId);

        if ($sticker == null) {
            return null;
        }

        return $sticker->images;
    }
}
