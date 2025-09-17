<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Http\Resources\v1;

use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getMobileSettings(): array
    {
        return [
            MetaFoxConstant::PRIVACY_ICON => [
                MetaFoxPrivacy::EVERYONE           => MetaFoxPrivacy::PRIVACY_PUBLIC_MOBILE_ICON,
                MetaFoxPrivacy::MEMBERS            => MetaFoxPrivacy::PRIVACY_MEMBERS_MOBILE_ICON,
                MetaFoxPrivacy::FRIENDS            => MetaFoxPrivacy::PRIVACY_FRIENDS_MOBILE_ICON,
                MetaFoxPrivacy::FRIENDS_OF_FRIENDS => MetaFoxPrivacy::PRIVACY_FRIENDS_OF_FRIENDS_MOBILE_ICON,
                MetaFoxPrivacy::ONLY_ME            => MetaFoxPrivacy::PRIVACY_ONLY_ME_MOBILE_ICON,
                MetaFoxPrivacy::CUSTOM             => MetaFoxPrivacy::PRIVACY_CUSTOM_MOBILE_ICON,
            ],
        ];
    }

    public function getWebSettings(): array
    {
        return [
            MetaFoxConstant::PRIVACY_ICON => [
                MetaFoxPrivacy::EVERYONE           => MetaFoxPrivacy::PRIVACY_PUBLIC_ICON,
                MetaFoxPrivacy::MEMBERS            => MetaFoxPrivacy::PRIVACY_MEMBERS_ICON,
                MetaFoxPrivacy::FRIENDS            => MetaFoxPrivacy::PRIVACY_FRIENDS_ICON,
                MetaFoxPrivacy::FRIENDS_OF_FRIENDS => MetaFoxPrivacy::PRIVACY_FRIENDS_OF_FRIENDS_ICON,
                MetaFoxPrivacy::ONLY_ME            => MetaFoxPrivacy::PRIVACY_ONLY_ME_ICON,
                MetaFoxPrivacy::CUSTOM             => MetaFoxPrivacy::PRIVACY_CUSTOM_ICON,
            ],
        ];
    }
}
