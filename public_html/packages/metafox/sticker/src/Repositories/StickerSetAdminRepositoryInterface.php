<?php

namespace MetaFox\Sticker\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Sticker\Models\StickerSet;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface StickerSet.
 * @mixin BaseRepository
 * @method StickerSet find($id, $columns = ['*'])
 * @method StickerSet getModel()
 */
interface StickerSetAdminRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewStickerSets(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return StickerSet
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createStickerSet(User $context, array $attributes): StickerSet;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return StickerSet
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function updateStickerSet(User $context, int $id, array $attributes): StickerSet;

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
     *
     * @return bool
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function deleteStickerSet(User $context, int $id): bool;

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
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return StickerSet
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function installStickerSet(User $context, array $attributes): StickerSet;

    /**
     * @param StickerSet $stickerSet
     * @param string     $action
     */
    public function checkCanUpdate(StickerSet $stickerSet, string $action = 'update'): void;
}
