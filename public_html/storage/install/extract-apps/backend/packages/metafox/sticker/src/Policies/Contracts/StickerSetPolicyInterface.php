<?php

namespace MetaFox\Sticker\Policies\Contracts;

use MetaFox\Platform\Contracts\User;
use MetaFox\Sticker\Models\StickerSet;

/**
 * Interface StickerSetPolicyInterface.
 *
 * @ignore
 * @codeCoverageIgnore
 */
interface StickerSetPolicyInterface
{
    /**
     * @param User       $user
     * @param StickerSet $resource
     *
     * @return bool
     */
    public function view(User $user, StickerSet $resource): bool;

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool;

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool;

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool;

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function addUserStickerSet(User $user): bool;

    /**
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool;
}
