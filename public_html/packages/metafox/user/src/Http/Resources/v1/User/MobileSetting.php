<?php

namespace MetaFox\User\Http\Resources\v1\User;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;
use MetaFox\User\Support\Facades\User;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->apiUrl('user')
            ->apiParams([
                'q'                => ':q',
                'sort'             => ':sort',
                'country'          => ':country',
                'country_state_id' => ':country_state_id',
                'gender'           => ':gender',
                'city_code'        => ':city_code',
                'is_featured'      => ':is_featured',
                'group'            => ':group',
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
            ->apiParams([
                'q'                => ':q',
                'sort'             => ':sort',
                'country'          => ':country',
                'country_state_id' => ':country_state_id',
                'gender'           => ':gender',
                'city_code'        => ':city_code',
                'is_featured'      => ':is_featured',
                'group'            => ':group',
            ])
            ->apiRules(User::getAllowApiRules());

        $this->add('editItem')
            ->apiUrl('core/mobile/form/user.profile/:id');

        $this->add('viewItem')
            ->apiUrl('user/:id')
            ->pageUrl('user/:id');

        $this->add('deleteItem')
            ->apiUrl('user/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('user::phrase.delete_confirm'),
                ]
            );

        /*
         * @deprecated Remove in 5.2.0
         */
        $this->add('featureItem')
            ->apiUrl('user/feature/:id');

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

        $this->add('editAccountInfo')
            ->asGet()
            ->apiUrl('core/mobile/form/user.account.info');

        $this->add('editAccountPassword')
            ->asGet()
            ->apiUrl('core/mobile/form/user.account.edit_password');

        $this->add('editAccountLanguage')
            ->asGet()
            ->apiUrl('core/mobile/form/user.account.edit_language_id');

        $this->add('viewRecommendUsers')
            ->apiUrl('user')
            ->apiParams([
                'view' => 'recommend',
            ]);
        $this->add('viewRecentUsers')
            ->apiUrl('user')
            ->apiParams([
                'view' => 'recent',
            ]);
        $this->add('viewFeaturedUsers')
            ->apiUrl('user')
            ->apiParams([
                'view' => 'featured',
            ]);

        $this->add('filterMember')
            ->asGet()
            ->apiUrl('core/mobile/form/user.search');

        $this->add('searchGlobalUser')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'       => 'user',
                'q'          => ':q',
                'is_hashtag' => ':is_hashtag',
            ]);

        $this->add('getReviewTagForm')
            ->apiUrl('core/mobile/form/user.account.review_tag');

        $this->add('follow')
            ->apiUrl('follow')
            ->asPost()
            ->apiParams([
                'user_id' => ':user_id',
            ]);

        $this->add('unfollow')
            ->apiUrl('follow/:id')
            ->asDelete();

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

        $this->add('updateSmsNotificationSettings')
            ->apiUrl('account/notification')
            ->asPut()
            ->apiParams([
                'channel' => 'sms',
            ]);

        $this->add('getCancelAccountForm')
            ->apiUrl('core/mobile/form/user.account.cancel/:id');

        $this->add('getGatewaySettings')
            ->asGet()
            ->apiUrl('core/mobile/form/payment.account.setting');

        $this->add('getAccountSettings')
            ->apiUrl('account/setting')
            ->asGet();

        $this->add('getAccountSettingForm')
            ->apiUrl('core/mobile/form/user.account.edit_:name/:id')
            ->asGet();

        $this->add('getEmailSettingForm')
            ->asGet()
            ->apiParams([
                'resolution' => 'mobile',
            ])
            ->apiUrl('user/account/email-form');

        $this->add('getPhoneNumberSettingForm')
            ->asGet()
            ->apiParams([
                'resolution' => 'mobile',
            ])
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

        $this->add('userInfo')
            ->apiUrl('user/info/:id')
            ->asGet();

        $this->add('getRegisterForm')
            ->apiUrl('core/mobile/form/user.register')
            ->apiParams([
                'code'        => ':code',
                'invite_code' => ':invite_code',
            ]);

        $this->add('logoutOtherDevices')
            ->apiUrl('authorization/device/logout-all')
            ->asPatch()
            ->confirm([
                'title'   => __p('user::phrase.account_setting_label.logout_other_devices'),
                'message' => __p('user::phrase.confirm_logout_other_device'),
            ]);

        $this->add('getPendingActions')
            ->asGet()
            ->apiUrl('user/pending-actions');

        $this->add('approveItem')
            ->apiUrl('user/approve/:id')
            ->asPatch();

        $this->add('deniedItem')
            ->apiUrl('core/mobile/form/user.deny/:id')
            ->asGet();
    }
}
