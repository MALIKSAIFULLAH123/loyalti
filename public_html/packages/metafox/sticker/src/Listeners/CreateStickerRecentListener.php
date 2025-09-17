<?php

namespace MetaFox\Sticker\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Sticker\Models\StickerRecent;
use MetaFox\Sticker\Repositories\StickerRecentRepositoryInterface;

/**
 * Class CreateStickerRecentListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CreateStickerRecentListener
{
    /**
     * @param User $context
     * @param int  $stickerId
     *
     * @return StickerRecent
     */
    public function handle(User $context, int $stickerId): StickerRecent
    {
        return resolve(StickerRecentRepositoryInterface::class)->createRecentSticker($context, $stickerId);
    }
}
