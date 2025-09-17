<?php

namespace MetaFox\Authorization\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateRootParentIdJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        $roles = Role::all();

        if (!$roles->count()) {
            return;
        }

        $customRoles = $roles->filter(function (Role $role) {
            return $role->parent_id > 0;
        });

        $defaultRoles = $roles->filter(function (Role $role) {
            return $role->parent_id == 0;
        });

        if (!$customRoles->count()) {
            return;
        }

        $defaultRoles = $defaultRoles->keyBy('id');

        $inheritedTracking = [];

        $customRoles->sortBy(['parent_id', 'id'])
            ->values()
            ->each(function (Role $role) use ($defaultRoles, &$inheritedTracking) {
                if (Arr::has($inheritedTracking, $role->parent_id)) {
                    Arr::set($inheritedTracking, $role->entityId(), Arr::get($inheritedTracking, $role->parent_id));
                    return;
                }

                /**
                 * @var Role|null $defaultRole
                 */
                if (!($defaultRole = $defaultRoles->get($role->parent_id)) instanceof Role) {
                    return;
                }

                Arr::set($inheritedTracking, $role->entityId(), $defaultRole->entityId());
            });

        if (!count($inheritedTracking)) {
            return;
        }

        $customRoles->each(function (Role $role) use ($inheritedTracking) {
            if (null === ($rootParentId = Arr::get($inheritedTracking, $role->entityId()))) {
                return;
            }

            $role->update(['root_parent_id' => $rootParentId]);
        });
    }
}
