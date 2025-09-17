<?php

namespace MetaFox\Core\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Link.
 * @mixin BaseRepository
 */
interface LinkRepositoryInterface
{
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
