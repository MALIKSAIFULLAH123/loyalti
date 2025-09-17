<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\TourGuide\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;
use MetaFox\TourGuide\Models\TourGuide;
use MetaFox\TourGuide\Supports\Constants;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getUserPermissions(): array
    {
        return [
            TourGuide::ENTITY_TYPE => [
                'create' => UserRole::LEVEL_ADMINISTRATOR,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'tour_guide_button' => [
                'value' => Constants::DEFAULT_TOUR_GUIDE_BUTTON_POSITION,
                'type'  => 'array',
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'user.pending_subscription.allow_endpoints' => [AllowEndpointsPendingSubscriptionListener::class],
        ];
    }
}
