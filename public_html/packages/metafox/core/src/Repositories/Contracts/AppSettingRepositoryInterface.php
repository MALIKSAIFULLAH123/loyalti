<?php

namespace MetaFox\Core\Repositories\Contracts;

use Illuminate\Http\Request;
use MetaFox\Authorization\Models\Role;

interface AppSettingRepositoryInterface
{
    /**
     * @param  Request              $request
     * @param  Role                 $role
     * @return array<string, mixed>
     */
    public function getMobileSettings(Request $request, Role $role): array;

    /**
     * @param  Request              $request
     * @param  Role                 $role
     * @return array<string, mixed>
     */
    public function getWebSettings(Request $request, Role $role): array;

    /**
     * @param Role   $role
     * @param string $type
     * @param string $resolution
     * @return array
     */
    public function getSettingsByType(Role $role, string $type, string $resolution): array;

    /**
     * @param  Request              $request
     * @param  Role                 $role
     * @return array<string, mixed>
     */
    public function getAdminSettings(Request $request, Role $role): array;
}
