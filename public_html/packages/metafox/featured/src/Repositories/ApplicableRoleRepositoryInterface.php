<?php

namespace MetaFox\Featured\Repositories;

use MetaFox\Featured\Models\Package;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ApplicableRole
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ApplicableRoleRepositoryInterface
{
    /**
     * @param Package $package
     * @param array   $roleIds
     * @return bool
     */
    public function updateForPackage(Package $package, array $roleIds): bool;

    /**
     * @param Package $package
     * @return bool
     */
    public function deleteForPackage(Package $package): bool;
}
