<?php

namespace MetaFox\Mfa\Repositories;

use MetaFox\Mfa\Models\EnforceRequest;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface EnforceRequestRepositoryInterface.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface EnforceRequestRepositoryInterface
{
    /**
     * Create a new enforcement request.
     * @param User $user
     * @param array $attributes
     *
     * @return EnforceRequest
     */
    public function createRequest(User $user, array $attributes): EnforceRequest;

    /**
     * Get active enforcement request.
     * @param User $user
     *
     * @return EnforceRequest|null
     */
    public function getActiveRequest(User $user): ?EnforceRequest;
}
