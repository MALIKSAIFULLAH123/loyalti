<?php

namespace MetaFox\Sticker\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface StickerSet.
 * @mixin BaseRepository
 * @method StickerSet find($id, $columns = ['*'])
 * @method StickerSet getModel()
 */
interface StickerSetRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewStickerSetsAll(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewStickerSetsUser(User $context, array $attributes): Paginator;

    /**
     * @param int $userId
     * @param int $stickerSetId
     *
     * @return bool
     */
    public function checkStickerSetAdded(int $userId, int $stickerSetId): bool;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return StickerSet
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function viewStickerSet(User $context, int $id): StickerSet;

    /**
     * @param User $context
     * @param int  $id
     * @param int  $isActive
     *
     * @return bool
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function toggleActive(User $context, int $id, int $isActive): bool;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function deleteSticker(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addUserStickerSet(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function deleteUserStickerSet(User $context, int $id): bool;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function getStickers(User $context, array $attributes): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function markAsDefault(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function removeDefault(User $context, int $id): bool;

    /**
     * @param StickerSet $stickerSet
     * @param int        $thumbnailId
     */
    public function updateThumbnail(StickerSet $stickerSet, int $thumbnailId = 0): void;

    /**
     * @param User            $context
     * @param array<int, int> $orders
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function orderingStickerSet(User $context, array $orders): bool;

    /**
     * @param User            $context
     * @param array<int, int> $orders
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function orderingSticker(User $context, array $orders): bool;

    /**
     * @param int $stickerId
     *
     * @return Sticker|null
     */
    public function getSticker(int $stickerId): ?Sticker;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewStickerSets(User $context, array $attributes): Paginator;

    /**
     * @param User $user
     * @return void
     */
    public function addDefaultStickerForUser(User $user): void;

    /**
     * @param StickerSet $stickerSet
     * @return void
     */
    public function checkIsDeleted(StickerSet $stickerSet): void;
}
