<?php

namespace MetaFox\Saved\Repositories;

use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SavedAggRepositoryInterface.
 *
 * @mixin BaseRepository
 */
interface SavedAggRepositoryInterface
{
    /**
     * @param User $user
     *
     * @return void
     */
    public function deleteForUser(User $user);
}
