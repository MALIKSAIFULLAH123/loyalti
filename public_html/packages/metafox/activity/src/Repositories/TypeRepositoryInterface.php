<?php

namespace MetaFox\Activity\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Models\Type;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Throwable;
use Illuminate\Contracts\Pagination\Paginator;

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

    /**
     * @param  string $type
     * @return ?Type
     */
    public function getTypeByType(string $type): ?Type;

    /**
     * Get activated activity types.
     * @return array<string>
     */
    public function getActiveTypeValues(): array;

    /**
     * @return array
     */
    public function getActiveTypeOptions(): array;

    /**
     * @return array
     */
    public function getActiveEntityTypeOptions(): array;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewTypes(User $context, array $attributes = []): Paginator;
}
