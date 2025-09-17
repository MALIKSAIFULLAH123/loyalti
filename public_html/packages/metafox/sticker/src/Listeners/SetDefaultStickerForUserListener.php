<?php

namespace MetaFox\Sticker\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;

/**
 * Class SetDefaultStickerForUserListener.
 * @ignore
 * @codeCoverageIgnore
 */
class SetDefaultStickerForUserListener
{
    /**
     * @param User $context
     */
    public function handle(User $context): void
    {
        $this->repository()->addDefaultStickerForUser($context);
    }

    protected function repository(): StickerSetRepositoryInterface
    {
        return resolve(StickerSetRepositoryInterface::class);
    }
}
