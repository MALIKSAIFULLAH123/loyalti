<?php

namespace MetaFox\Featured\Repositories\Eloquent;

use MetaFox\Featured\Models\Package;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Featured\Repositories\ApplicableRoleRepositoryInterface;
use MetaFox\Featured\Models\ApplicableRole;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class ApplicableRoleRepository
 *
 */
class ApplicableRoleRepository extends AbstractRepository implements ApplicableRoleRepositoryInterface
{
    public function model()
    {
        return ApplicableRole::class;
    }

    public function updateForPackage(Package $package, array $roleIds): bool
    {
        $current = $package->role_ids->pluck('role_id')->toArray();
        $new    = array_diff($roleIds, $current);
        $remove = array_diff($current, $roleIds);

        if (!count($new) && !count($remove)) {
            return true;
        }

        if (count($new)) {
            $map = array_map(function ($roleId) use ($package) {
                return [
                    'role_id' => $roleId,
                    'package_id' => $package->entityId(),
                ];
            }, $new);

            $this->getModel()->newQuery()
                ->upsert($map, ['package_id', 'role_id']);
        }

        if (count($remove)) {
            $this->getModel()->newQuery()
                ->where('package_id', '=', $package->entityId())
                ->whereIn('role_id', $remove)
                ->delete();
        }

        return true;
    }

    public function deleteForPackage(Package $package): bool
    {
        $this->getModel()->newQuery()
            ->where('package_id', '=', $package->entityId())
            ->delete();
    }
}
