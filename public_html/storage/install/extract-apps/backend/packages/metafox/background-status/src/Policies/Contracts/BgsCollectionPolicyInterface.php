<?php

namespace MetaFox\BackgroundStatus\Policies\Contracts;

use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\Platform\Contracts\User;

/**
 * Class BgsCollectionPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
interface BgsCollectionPolicyInterface
{
    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool;

    /**
     * Determine whether the user can view the model.
     *
     * @param User          $user
     * @param BgsCollection $resource
     *
     * @return bool
     */
    public function view(User $user, BgsCollection $resource): bool;

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool;

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool;

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool;
}
