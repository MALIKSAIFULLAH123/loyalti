<?php

namespace MetaFox\Platform\Support\Repository;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\HasPolicy;
use MetaFox\Platform\Contracts\User;

/**
 * Trait HasPendingMode
 * @package MetaFox\Platform\Support\Repository
 * @codeCoverageIgnore
 */
trait HasPendingMode
{
    /**
     * @param User $context
     * @param int  $id
     * @param int  $pendingMode
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function updatePendingMode(User $context, int $id, int $pendingMode): bool
    {
        $resource = $this->find($id);

        if ($resource instanceof HasPolicy) {
            gate_authorize($context, 'update', $resource, $resource);
        }

        return $resource->update(['pending_mode' => $pendingMode]);
    }
}
