<?php

namespace MetaFox\Activity\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Models\Share;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Share.
 * @mixin BaseRepository
 * @method Share find($id, $columns = ['*'])
 * @method Share getModel()
 */
interface ShareRepositoryInterface
{
    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return int
     * @throws AuthorizationException
     * @see ShareRequest
     */
    public function share(User $context, User $owner, array $attributes): int;

    /**
     * @param Share                $share
     * @param array<string, mixed> $attributes
     *
     * @return Share
     */
    public function updateShare(Share $share, array $attributes): Share;

    /**
     * @param  int  $userId
     * @return void
     */
    public function deleteUserData(int $userId): void;

    /**
     * @param  int  $ownerId
     * @return void
     */
    public function deleteOwnerData(int $ownerId): void;
}
