<?php

namespace MetaFox\ActivityPoint\Support;

use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\UserRole;

class PointSetting implements \MetaFox\ActivityPoint\Contracts\Support\PointSetting
{
    private PackageRepositoryInterface $moduleRepository;

    private PointSettingRepositoryInterface $settingRepository;

    private RoleRepositoryInterface $roleRepository;

    public function __construct(
        PackageRepositoryInterface $moduleRepository,
        RoleRepositoryInterface $roleRepository,
        PointSettingRepositoryInterface $settingRepository
    ) {
        $this->moduleRepository  = $moduleRepository;
        $this->settingRepository = $settingRepository;
        $this->roleRepository    = $roleRepository;
    }

    public function getAllowedRoleOptions(): array
    {
        $roles = $this->roleRepository->getRoleOptions();

        $disallowedRoleIds = [UserRole::SUPER_ADMIN_USER];

        return array_filter($roles, function ($role) use ($disallowedRoleIds) {
            return !in_array($role['value'], $disallowedRoleIds);
        });
    }

    public function getAllowedRole(): array
    {
        return Arr::pluck($this->getAllowedRoleOptions(), 'value');
    }
}
