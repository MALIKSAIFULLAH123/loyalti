<?php

namespace MetaFox\Sticker\Repositories;

use MetaFox\Platform\Contracts\User;
use MetaFox\Sticker\Models\StickerRecent as Model;

/**
 * Interface StickerSet.
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 */
interface StickerRecentRepositoryInterface
{
    /**
     * @param  User  $context
     * @param  int   $stickerId
     * @return Model
     */
    public function createRecentSticker(User $context, int $stickerId): Model;
}
