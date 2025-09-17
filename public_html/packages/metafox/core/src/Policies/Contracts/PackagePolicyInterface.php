<?php

namespace MetaFox\Core\Policies\Contracts;

use MetaFox\App\Models\Package;
use MetaFox\Platform\Contracts\User as User;

interface PackagePolicyInterface
{
    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function moderate(User $user): bool;

    /**
     * Determine whether the user can update the model.
     *
     * @param User                 $user
     * @param Package              $package
     * @param array<string, mixed> $params
     *
     * @return bool
     */
    public function update(User $user, Package $package, array $params): bool;
}
