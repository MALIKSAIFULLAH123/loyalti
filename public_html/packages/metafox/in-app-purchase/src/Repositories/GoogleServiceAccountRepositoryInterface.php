<?php

namespace MetaFox\InAppPurchase\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface UserStreamKey.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface GoogleServiceAccountRepositoryInterface
{
    /**
     * @param  array $attributes
     * @return bool
     */
    public function create(array $attributes): bool;
}
