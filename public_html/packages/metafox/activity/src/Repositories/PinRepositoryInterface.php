<?php

namespace MetaFox\Activity\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface Pin.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface PinRepositoryInterface
{
    /**
     * @param User $context
     * @param User $owner
     * @param int  $feedId
     *
     * @return bool
     * @throws AuthorizationException|ValidatorException
     */
    public function pin(User $context, User $owner, int $feedId): bool;

    /**
     * @param User $context
     * @param User $owner
     * @param int  $feedId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function unpin(User $context, User $owner, int $feedId): bool;

    /**
     * @param User $context
     * @param int  $feedId
     *
     * @return bool
     * @throws AuthorizationException|ValidatorException
     */
    public function pinHome(User $context, int $feedId): bool;

    /**
     * @param User $context
     * @param int  $feedId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function unpinHome(User $context, int $feedId): bool;

    /**
     * Get pins target by current user id.
     *
     * @param  User  $context
     * @param  int   $feedId
     * @return array
     */
    public function getPinOwnerIds(User $context, int $feedId): array;

    /**
     * @param  int   $ownerId
     * @return int[]
     */
    public function getPinsInProfilePage(int $ownerId): array;

    /**
     * @return int[]
     */
    public function getPinsInHomePage(): array;

    /**
     * @param  int|null $ownerId
     * @return void
     */
    public function clearCache(?int $ownerId = null): void;
}
