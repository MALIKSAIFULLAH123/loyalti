<?php

namespace MetaFox\Platform\Support\Repository\Contracts;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;

/**
 * Interface HasPendingMode.
 */
interface HasPendingMode
{
    /**
     * @param User $context
     * @param int  $id
     * @param int  $pendingMode
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function updatePendingMode(User $context, int $id, int $pendingMode): bool;
}
