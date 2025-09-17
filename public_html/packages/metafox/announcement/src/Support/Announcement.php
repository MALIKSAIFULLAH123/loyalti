<?php

namespace MetaFox\Announcement\Support;

use Illuminate\Support\Arr;
use MetaFox\Announcement\Contracts\Support\Announcement as AnnouncementContract;
use MetaFox\Announcement\Repositories\StyleRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\UserRole;

class Announcement implements AnnouncementContract
{
    private StyleRepositoryInterface $styleRepository;
    private RoleRepositoryInterface $roleRepository;

    public function __construct(StyleRepositoryInterface $styleRepository, RoleRepositoryInterface $roleRepository)
    {
        $this->styleRepository = $styleRepository;
        $this->roleRepository  = $roleRepository;
    }

    /**
     * @inheritDoc
     */
    public function getStyleOptions(): array
    {
        return $this->styleRepository->getStyleOptions();
    }

    public function getAllowedRoleOptions(): array
    {
        $roles = $this->roleRepository->getRoleOptions();

        $disallowedRoleIds = [UserRole::SUPER_ADMIN_USER, UserRole::GUEST_USER, UserRole::BANNED_USER];

        return array_filter($roles, function ($role) use ($disallowedRoleIds) {
            return !in_array($role['value'], $disallowedRoleIds);
        });
    }

    public function getAllowedRole(): array
    {
        return Arr::pluck($this->getAllowedRoleOptions(), 'value');
    }
}
