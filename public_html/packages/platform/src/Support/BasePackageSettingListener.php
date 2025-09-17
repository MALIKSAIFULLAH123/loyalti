<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Support;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Platform\Contracts\PackageSettingListenerInterface;

/**
 * Class BasePackageSettingListener.
 * @method registerApplicationSchedule(Schedule $schedule)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BasePackageSettingListener implements PackageSettingListenerInterface
{
    public function getPolicies(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getCaptchaRules(): array
    {
        return [];
    }

    public function getPolicyHandlers(): array
    {
        return [];
    }

    public function getActivityTypes(): array
    {
        return [];
    }

    public function getActivityForm(): array
    {
        return [];
    }

    public function getNotificationTypes(): array
    {
        return [];
    }

    public function getSearchTypes(): array
    {
        return [];
    }

    public function getUserPermissions(): array
    {
        return [];
    }

    public function getUserValuePermissions(): array
    {
        return [];
    }

    public function getSiteSettings(): array
    {
        return [];
    }

    public function getEvents(): array
    {
        return [];
    }

    public function getUserPrivacy(): array
    {
        return [];
    }

    public function getUserPrivacyResource(): array
    {
        return [];
    }

    public function getDefaultPrivacy(): array
    {
        return [];
    }

    public function getProfileMenu(): array
    {
        return [];
    }

    public function getAppSettings()
    {
        return null;
    }

    public function handle(string $action)
    {
        if (!method_exists($this, $action)) {
            return false;
        }

        return $this->{$action}();
    }

    public function getUserValues(): array
    {
        return [];
    }

    public function getItemTypes(): array
    {
        return [];
    }

    /**
     * @return array<string>|null
     */
    public function getSiteStatContent(): ?array
    {
        return null;
    }

    public function getSavedTypes(): array
    {
        return [];
    }

    /**
     * return list of classname of checkers.
     * @return array
     */
    public function getCheckers(): array
    {
        return [];
    }

    public function getAdMobPages(): array
    {
        return [];
    }
}
