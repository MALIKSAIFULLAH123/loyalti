<?php

namespace MetaFox\Authorization\Listeners;

use MetaFox\Authorization\Repositories\PermissionSettingRepositoryInterface;

class DeletePermissionListener
{
    public function handle(string $moduleId, string $name, string $entityType, string $guard = 'api'): void
    {
        resolve(PermissionSettingRepositoryInterface::class)->deletePermission($moduleId, $name, $entityType, $guard);
    }
}
