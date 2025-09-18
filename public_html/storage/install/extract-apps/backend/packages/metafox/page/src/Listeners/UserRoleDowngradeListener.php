<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\UserRole;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

class UserRoleDowngradeListener
{
    public function __construct(protected UserPrivacyRepositoryInterface $repository)
    {
    }

    public function handle(User $context, User $user): void
    {
        $this->handlePageClaimSetting($user);
    }

    protected function handlePageClaimSetting(User $user): void
    {
        $settingName         = 'page.admin_in_charge_of_page_claims';
        $notificationType    = 'claim_page';
        $userRole            = resolve(RoleRepositoryInterface::class)->roleOf($user);
        $userId              = $user->entityId();
        $valueSetting        =  Settings::get($settingName, []);

        if (empty($valueSetting)) {
            return;
        }

        if (!in_array($userId, $valueSetting)) {
            return;
        }

        if ($userRole->entityId() != UserRole::ADMIN_USER_ID) {
            $arrayValue = array_values(array_filter($valueSetting, function ($value, $key) use ($userId) {
                return $value != $userId;
            }, ARRAY_FILTER_USE_BOTH));

            Settings::save([$settingName => $arrayValue]);

            app('events')->dispatch('notification.delete_notification_by_type_and_notifiable', [
                $notificationType, $user->entityId(), $user->entityType(),
            ]);
        }
    }
}
