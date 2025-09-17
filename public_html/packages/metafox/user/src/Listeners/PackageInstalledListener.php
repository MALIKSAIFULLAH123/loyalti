<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use MetaFox\Authorization\Repositories\PermissionSettingRepositoryInterface;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\User\Database\Factories\CancelReasonFactory;
use MetaFox\User\Models\CancelReason;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

/**
 * handle package installed.
 *
 * Class PackageInstalledListener
 */
class PackageInstalledListener
{
    public function handle(string $package)
    {
        /** @var BasePackageSettingListener $listener */
        $listener = PackageManager::getListener($package);

        if (!$listener) {
            return;
        }

        $appId = PackageManager::getAlias($package);

        $this->setUpUserPrivacyType();

        $this->setUpUserPermission($listener, $appId);

        $this->setUpUserPermissionValue($listener, $appId);

        $this->rollDownPermissions($listener, $appId);

        $this->setUpUserCancelReason($appId);
    }

    protected function setUpUserPermission(BasePackageSettingListener $listener, string $appId): void
    {
        /** @var PermissionSettingRepositoryInterface $service */
        $service = resolve(PermissionSettingRepositoryInterface::class);

        $service->installSettings($appId, $listener->getUserPermissions());
    }

    protected function setUpUserPrivacyType(): void
    {
        /** @var UserPrivacyRepositoryInterface $privacyService */
        $privacyService = resolve(UserPrivacyRepositoryInterface::class);

        $privacyService->installPrivacyTypes();
    }

    protected function setUpUserPermissionValue(BasePackageSettingListener $listener, string $appId): void
    {
        /** @var PermissionSettingRepositoryInterface $service */
        $service = resolve(PermissionSettingRepositoryInterface::class);

        $settings = $listener->getUserValuePermissions();

        if (empty($settings) || !is_string($appId)) {
            return;
        }

        $service->installValueSettings($appId, $settings);
    }

    protected function rollDownPermissions(BasePackageSettingListener $listener, string $appId)
    {
        $service         = resolve(PermissionSettingRepositoryInterface::class);
        $names           = [];
        $permission      = $listener->getUserPermissions();
        $permissionValue = $listener->getUserValuePermissions();

        foreach ($permission as $resource => $data) {
            foreach ($data as $key => $value) {
                $names[] = "$resource.$key";
            }
        }

        foreach ($permissionValue as $resource => $data) {
            foreach ($data as $key => $value) {
                $names[] = "$resource.$key";
            }
        }

        $service->rollDownPermissions($appId, $names);
    }

    protected function setUpUserCancelReason(string $appId): void
    {
        if ('user' !== $appId) {
            return;
        }

        $superAdmin = resolve(UserRepositoryInterface::class)->getSuperAdmin();
        if (!$superAdmin instanceof User) {
            return;
        }

        // @todo: BA need define popular cancel reason
        $data = [
            'user::phrase.other',
            'user::phrase.this_is_temporary',
            'user::phrase.my_account_was_hacked',
            'user::phrase.spending_too_much_time',
            'user::phrase.privacy_concern',
        ];

        foreach ($data as $key => $phrase) {
            $seeded = CancelReason::query()->where('phrase_var', $phrase)->exists();
            if ($seeded) {
                continue;
            }

            CancelReasonFactory::new()
                ->createQuietly([
                    'user_id'    => $superAdmin->entityId(),
                    'user_type'  => $superAdmin->entityType(),
                    'owner_id'   => $superAdmin->entityId(),
                    'owner_type' => $superAdmin->entityType(),
                    'phrase_var' => $phrase,
                    'ordering'   => $key + 1,
                ]);
        }
    }
}
