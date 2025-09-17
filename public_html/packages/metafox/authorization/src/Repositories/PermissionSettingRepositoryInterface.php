<?php

namespace MetaFox\Authorization\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Authorization\Models\Role;

interface PermissionSettingRepositoryInterface
{
    /*
     * Format:
     * [
     *   'blog' => [
     *      'create' => [ 'Admin', 'User'],
     *      'edit' => [ 'Admin', 'User'],
     *    ],
     *    'activity' => [
     *      'create' => [ 'Admin', 'User'],
     *      'edit' => [ 'Admin', 'User'],
     *    ],
     * ]
     */
    public function installSettingsFromApps(): bool;

    /**
     * @param string               $moduleId
     * @param array<string, mixed> $resourceSettings
     */
    public function installSettings(string $moduleId, array $resourceSettings): bool;

    /**
     * [
     *    'blog' => [
     *      'activity_point.create' => [
     *          'type' => '',
     *          'default' => 1,
     *          'roles' => [
     *              UserRole::ADMIN_USER  => 1,
     *              UserRole::STAFF_USER  => 1,
     *              UserRole::NORMAL_USER => 1,
     *         ]
     *      ]
     *   ]
     * ].
     * @return bool
     */
    public function installValueSettingsFromApps(): bool;

    /**
     * @param string                              $moduleId
     * @param array<string, array<string, mixed>> $resourceSettings
     *
     * @return bool
     */
    public function installValueSettings(string $moduleId, array $resourceSettings): bool;

    /**
     * @param Role $role
     *
     * @return array<mixed>
     *
     *                     [
     *                          'module' => [
     *                              'resource' => [
     *                                  'action_1' => true,
     *                                  'action_2' => 1,
     *                              ]
     *                          ]
     *                     ]
     */
    public function getPermissions(Role $role): array;

    /**
     * Ignore some action from permission form.
     * @return array
     */
    public function getExcludedActions(): array;

    /**
     * @param  string        $moduleId
     * @param  array<string> $notIn
     * @return void
     */
    public function rollDownPermissions(string $moduleId, array $notIn): void;

    /**
     * @param  string $moduleId
     * @param  string $name
     * @param  string $entityType
     * @param  string $guard
     * @return bool
     */
    public function deletePermission(string $moduleId, string $name, string $entityType, string $guard = Role::DEFAULT_GUARD): bool;
}
