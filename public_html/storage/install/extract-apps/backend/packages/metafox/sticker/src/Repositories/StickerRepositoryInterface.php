<?php

namespace MetaFox\Sticker\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;

/**
 * Interface StickerSet.
 * @method Sticker find($id, $columns = ['*'])
 * @method Sticker getModel()
 */
interface StickerRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewStickers(User $context, array $attributes): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     */
    public function deleteSticker(User $context, int $id): bool;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewRecentStickers(User $context, array $attributes): Paginator;

    /**
     * @param  User       $context
     * @param  StickerSet $stickerSet
     * @param  array      $attributes
     * @return void
     */
    public function uploadStickers(User $context, StickerSet $stickerSet, array $attributes): void;
}
