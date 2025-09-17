<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\User;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\User\Support\Facades\User;

/**
 *--------------------------------------------------------------------------
 * User Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class UserWebSetting.
 * @@SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->pageUrl('user/search')
            ->pageParams([
                'view' => ':tab',
            ])
            ->placeholder(__p('user::phrase.search_users'));

        $this->add('unblockItem')
            ->apiUrl('account/blocked-user/:id')
            ->asDelete();

        $this->add('blockItem')
            ->apiUrl('account/blocked-user')
            ->asPost()
            ->apiParams(['user_id' => ':id'])
            ->confirm([
                'title'   => __p('core::phrase.are_you_sure'),
                'message' => __p('user::phrase.block_user_confirm'),
            ]);

        $this->add('viewAll')
            ->apiUrl('user')
            ->apiRules(User::getAllowApiRules());

        $this->add('editItem')
            ->pageUrl('user/profile')
            ->apiUrl('user/profile/form');

        $this->add('viewItem')
            ->apiUrl('user/:id')
            ->urlParams([':id' => 'id'])
            ->pageUrl('user/:id');

        $this->add('deleteItem')
            ->apiUrl('user/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('user::phrase.delete_confirm'),
                ]
            );

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('user/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('user/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('updateAvatar')
            ->apiUrl('user/avatar/:id');

        $this->add('updateProfileCover')
            ->apiUrl('user/cover/:id')
            ->asPost();

        $this->add('removeProfileCover')
            ->apiUrl('user/remove-cover/:id')
            ->asPut()
            ->confirm(['message' => __p('user::phrase.are_you_sure_you_want_to_delete_this_photo')]);

        $this->add('sendRequest')
            ->asPost()
            ->apiUrl('friend/request?friend_user_id=:id');

        $this->add('viewFriends')
            ->asGet()
            ->apiUrl('friend/?user_id=:id')
            ->apiRules([
                'q'    => ['truthy', 'q'],
                'sort' => [
                    'includes', 'sort', [Browse::SORT_RECENT, SortScope::SORT_FULL_NAME],
                ],
            ]);

        $this->add('cancelRequest')
            ->asDelete()
            ->apiUrl('friend/request/:id');

        $this->add('unfriend')
            ->asDelete()
            ->apiUrl('friend/:id')
            ->confirm(['message' => __p('user::phrase.unfriend_confirm_message')]);

        $this->add('acceptFriendRequest')
            ->asPut()
            ->apiUrl('friend/request/:id');

        $this->add('denyFriendRequest')
            ->asPut()
            ->apiUrl('friend/request/:id');

        $this->add('getInvisibleSettings')
            ->asGet()
            ->apiUrl('account/invisible');

        $this->add('updateInvisibleSettings')
            ->asPut()
            ->apiUrl('account/invisible');

        $this->add('getProfileSettings')
            ->asGet()
            ->apiUrl('account/profile-privacy/:id');

        $this->add('updateProfileSettings')
            ->asPut()
            ->apiUrl('account/profile-privacy');

        $this->add('getProfileMenuSettings')
            ->asGet()
            ->apiUrl('account/profile-menu/:id');

        $this->add('updateProfileMenuSettings')
            ->asPut()
            ->apiUrl('account/profile-menu');

        $this->add('getItemPrivacySettings')
            ->asGet()
            ->apiUrl('account/item-privacy/:id');

        $this->add('updateItemPrivacySettings')
            ->asPut()
            ->apiUrl('account/item-privacy');

        $this->add('getRegisterForm')
            ->apiUrl('core/form/user.register')
            ->apiParams([
                'code'        => ':code',
                'invite_code' => ':invite_code',
            ]);

        $this->add('getEmailNotificationSettings')
            ->apiUrl('account/notification')
            ->asGet()
            ->apiParams([
                'channel' => 'mail',
            ]);

        $this->add('getNotificationSettings')
            ->apiUrl('account/notification')
            ->asGet()
            ->apiParams([
                'channel' => 'database',
            ]);

        $this->add('getSmsNotificationSettings')
            ->apiUrl('account/notification')
            ->asGet()
            ->apiParams([
                'channel' => 'sms',
            ]);

        $this->add('updateEmailNotificationSettings')
            ->apiUrl('account/notification')
            ->asPut()
            ->apiParams([
                'channel' => ':channel',
            ]);

        $this->add('updateNotificationSettings')
            ->apiUrl('account/notification')
            ->asPut()
            ->apiParams([
                'channel' => ':channel',
            ]);

        $this->add('follow')
            ->apiUrl('follow')
            ->asPost()
            ->apiParams([
                'user_id' => ':user_id',
            ]);

        $this->add('unfollow')
            ->apiUrl('follow/:id')
            ->asDelete();

        $this->add('updateSmsNotificationSettings')
            ->apiUrl('account/notification')
            ->asPut()
            ->apiParams([
                'channel' => 'sms',
            ]);

        $this->add('getCancelAccountForm')
            ->apiUrl('core/form/user.account.cancel/:id')
            ->asGet();

        $this->add('getPasswordRequestForm')
            ->apiUrl('core/form/user.forgot_password')
            ->asGet();

        $this->add('getPasswordRequestMethodForm')
            ->apiUrl('core/form/user.password.request_method')
            ->apiParams([
                'email' => ':email',
            ])
            ->asGet();

        $this->add('getPasswordVerifyRequestForm')
            ->apiUrl('core/form/user.password.verify_request')
            ->apiParams([
                'user_id'        => ':user_id',
                'request_method' => ':request_method',
            ])
            ->asGet();

        $this->add('getPasswordResetForm')
            ->apiUrl('core/form/user.password.edit')
            ->apiParams([
                'user_id' => ':user_id',
                'token'   => ':token',
            ])
            ->asGet();

        $this->add('getPasswordLogoutAllForm')
            ->apiUrl('core/form/user.password.logout_all')
            ->apiParams([
                'user_id' => ':user_id',
                'token'   => ':token',
            ])
            ->asGet();

        $this->add('getAccountSettings')
            ->apiUrl('account/setting')
            ->asGet();

        $this->add('updateThemePreference')
            ->apiUrl('account/setting')
            ->apiParams([
                'profile_theme_type' => ':profile_theme_type',
                'profile_theme_id'   => ':profile_theme_id',
            ])
            ->asPut();

        $this->add('getAccountSettingForm')
            ->apiUrl('core/form/user.account.edit_:name/:id')
            ->asGet();

        $this->add('getEmailSettingForm')
            ->asGet()
            ->apiUrl('user/account/email-form');

        $this->add('getPhoneNumberSettingForm')
            ->asGet()
            ->apiUrl('user/account/phone-number-form');

        $this->add('exist')
            ->apiUrl('user/validate/identity')
            ->asPost()
            ->apiParams([
                'email'       => ':email',
                'user_name'   => ':user_name',
                'check_exist' => 1,
            ]);

        $this->add('validateEmailOrPhoneNumber')
            ->apiUrl('user/validate/identity')
            ->asPost()
            ->apiParams([
                'phone_number' => ':phone_number',
                'email'        => ':email',
            ]);

        $this->add('validatePhoneNumber')
            ->apiUrl('user/validate/phone-number')
            ->asPost()
            ->apiParams([
                'phone_number' => ':phone_number',
            ]);

        $this->add('getLanguageForm')
            ->apiUrl('core/form/user.account.language_form/:id')
            ->asGet();

        $this->add('updateAccountSetting')
            ->apiUrl('account/setting')
            ->asPut()
            ->apiParams([
                'language_id' => ':language_id',
            ]);

        $this->add('getVideoSettings')
            ->apiUrl('account/setting/video')
            ->asGet();

        $this->add('logoutOtherDevices')
            ->apiUrl('authorization/device/logout-all')
            ->asPatch()
            ->confirm([
                'title'   => __p('user::phrase.account_setting_label.logout_other_devices'),
                'message' => __p('user::phrase.confirm_logout_other_device'),
            ]);

        $this->add('editAccountPassword')
            ->asGet()
            ->apiUrl('core/form/user.account.edit_password');

        $this->add('getPendingActions')
            ->asGet()
            ->apiUrl('user/pending-actions');

        $this->add('approveItem')
            ->apiUrl('user/approve/:id')
            ->asPatch();

        $this->add('deniedItem')
            ->apiUrl('core/form/user.deny/:id')
            ->asGet();
    }
}
