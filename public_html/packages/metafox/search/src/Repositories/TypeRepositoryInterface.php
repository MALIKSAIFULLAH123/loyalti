<?php

namespace MetaFox\Search\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Search\Models\Type;
use Prettus\Repository\Eloquent\BaseRepository;
use Throwable;

/**
 * Interface Type.
 * @mixin BaseRepository
 */
interface TypeRepositoryInterface
{
    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Type
     *
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function updateType(User $context, int $id, array $attributes): Type;

    /**
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function deleteType(User $context, int $id): int;
}
