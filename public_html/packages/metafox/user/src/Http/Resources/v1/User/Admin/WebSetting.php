<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 *--------------------------------------------------------------------------
 * User Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting
 * Inject this class into property $resources.
 * @link \MetaFox\User\Http\Resources\v1\WebAppSetting::$resources;
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('editBasicInfo')
            ->asGet()
            ->apiUrl('admincp/core/form/user.update.basic_info/:id');

        $this->add('editCustomFields')
            ->asGet()
            ->apiUrl('admincp/core/form/user.update.custom_fields/:id');

        $this->add('editNotificationSetting')
            ->asGet()
            ->apiUrl('admincp/core/form/user.update.notification/:id');

        $this->add('editProfilePrivacy')
            ->asGet()
            ->apiUrl('admincp/core/form/user.update.privacy/:id');

        $this->add('logoutAllUsers')
            ->confirm([
                'title'   => __p('user::phrase.logout_all_users'),
                'message' => __p('user::phrase.logout_all_users_confirmation'),
            ])
            ->asPatch()
            ->apiUrl('admincp/user/logout-all-users');

        $this->add('validatePhoneNumber')
            ->apiUrl('user/validate/phone-number')
            ->asPost()
            ->apiParams([
                'phone_number' => ':phone_number',
            ]);
    }
}
