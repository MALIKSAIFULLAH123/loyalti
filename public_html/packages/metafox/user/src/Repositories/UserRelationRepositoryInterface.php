<?php

namespace MetaFox\User\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserRelation;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface UserRelation.
 * @mixin BaseRepository
 */
interface UserRelationRepositoryInterface
{
    /**
     * @param  User      $user
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewRelationShips(User $user, array $attributes): Paginator;

    /**
     * @param  User         $user
     * @param  array        $attributes
     * @return UserRelation
     */
    public function createRelationShip(User $user, array $attributes): UserRelation;

    /**
     * @param  User         $user
     * @param  array        $attributes
     * @return UserRelation
     */
    public function updateRelationShip(User $user, array $attributes): UserRelation;

    /**
     * @param  int          $id
     * @return UserRelation
     */
    public function activeRelation(int $id): UserRelation;

    /**
     * @return Collection
     */
    public function getRelations(): Collection;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteRelation(User $context, int $id): bool;
}
