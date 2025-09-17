<?php

namespace MetaFox\BackgroundStatus\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Validation\ValidationException;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface BgsCollectionRepositoryInterface.
 * @mixin BaseRepository
 * @method BgsCollection getModel()
 * @method BgsCollection find($id, $columns = ['*'])
 */
interface BgsCollectionRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewBgsCollectionsForAdmin(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewBgsCollectionsForFE(User $context, array $attributes): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return BgsCollection
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function viewBgsCollection(User $context, int $id): BgsCollection;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function getBackgrounds(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return BgsCollection
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createBgsCollection(User $context, array $attributes): BgsCollection;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return BgsCollection
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function updateBgsCollection(User $context, int $id, array $attributes): BgsCollection;

    /**
     * @param BgsCollection $bgsCollection
     * @param int           $mainBackgroundId
     */
    public function updateMainBackground(BgsCollection $bgsCollection, int $mainBackgroundId = 0): void;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function deleteBgsCollection(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function deleteBackground(User $context, int $id): bool;

    /**
     * @return int
     */
    public function getTotalCollectionActive(): int;
}
