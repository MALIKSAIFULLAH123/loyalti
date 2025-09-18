<?php

namespace MetaFox\Story\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Models\BackgroundSet;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface BackgroundSet.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @method BackgroundSet find($id, $columns = ['*'])
 * @method BackgroundSet getModel()
 */
interface BackgroundSetRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return Paginator
     */
    public function viewBackgroundSets(User $context, array $attributes): Paginator;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewBackgroundSetForFE(User $context, array $attributes): Paginator;

    /**
     * @param  User          $context
     * @param  int           $id
     * @return BackgroundSet
     */
    public function viewBackgroundSet(User $context, int $id): BackgroundSet;

    /**
     * @param  User          $context
     * @param  array         $attributes
     * @return BackgroundSet
     */
    public function createBackgroundSet(User $context, array $attributes): BackgroundSet;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return BackgroundSet
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function updateBackgroundSet(User $context, int $id, array $attributes): BackgroundSet;

    /**
     * @param BackgroundSet $backgroundSet
     * @param int           $mainBackgroundId
     */
    public function updateMainBackground(BackgroundSet $backgroundSet, int $mainBackgroundId = 0): void;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteBackgroundSet(User $context, int $id): bool;

    /**
     * @return BackgroundSet
     */
    public function getBackgroundSetActive(): BackgroundSet;
}
