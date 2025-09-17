<?php

namespace MetaFox\Authorization\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Authorization\Repositories\DeviceAdminRepositoryInterface;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Authorization\Repositories\Eloquent\DeviceAdminRepository;
use MetaFox\Authorization\Repositories\Eloquent\DeviceRepository;
use MetaFox\Authorization\Repositories\Eloquent\PermissionRepository;
use MetaFox\Authorization\Repositories\Eloquent\PermissionSettingRepository;
use MetaFox\Authorization\Repositories\Eloquent\RoleRepository;
use MetaFox\Authorization\Repositories\PermissionSettingRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub.
 */

/**
 * Class PackageServiceProvider.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    public array $singletons = [
        'perms'                                     => PermissionSettingRepositoryInterface::class,
        RoleRepositoryInterface::class              => RoleRepository::class,
        PermissionRepositoryInterface::class        => PermissionRepository::class,
        PermissionSettingRepositoryInterface::class => PermissionSettingRepository::class,
        DeviceAdminRepositoryInterface::class       => DeviceAdminRepository::class,
        DeviceRepositoryInterface::class            => DeviceRepository::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->callAfterResolving('reducer', function ($reducer) {
            $reducer->register([
                \MetaFox\Authorization\Support\LoadMissingUserRoles::class,
            ]);
        });
    }
}
