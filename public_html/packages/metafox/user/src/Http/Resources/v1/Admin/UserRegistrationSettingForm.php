<?php

namespace MetaFox\User\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AdminSettingForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\User as UserSupport;
use MetaFox\Yup\Yup;

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */
class UserRegistrationSettingForm extends AdminSettingForm
{
    protected function prepare(): void
    {
        $vars = [
            'user.allow_user_registration',
            'user.enable_auto_login_after_registration',
            'user.signup_repeat_password',
            // 'user.multi_step_registration_form',
            // 'user.profile_use_id',
            'user.enable_sms_registration',
            'user.enable_phone_number_registration',
            'user.verify_email_at_signup',
            'user.verification_timeout',
            'user.resend_verification_delay_time',
            'user.days_for_delete_pending_user_verification',
            'user.approve_users',
            'user.force_user_to_upload_on_sign_up',
            'user.on_register_privacy_setting',
            'user.on_signup_new_friend',
            'user.redirect_after_signup',
            'user.available_name_field_on_sign_up',
            // 'user.invite_only_community',
            'user.new_user_terms_confirmation',
            // 'user.require_basic_field',
            'user.on_register_user_group',
            // 'user.redirect_after_signup',
            'user.force_user_to_reenter_email',
            'user.shorter_reset_password_routine',
            'user.enable_opt_in_agreement',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('user::phrase.registration_settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/user'))
            ->asPost()
            ->setValue($value);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::switch('user.allow_user_registration')
                ->label(__p('user::admin.allow_user_registration_label'))
                ->description(__p('user::admin.allow_user_registration_desc')),
            Builder::switch('user.enable_auto_login_after_registration')
                ->label(__p('user::admin.enable_auto_login_after_registration_label'))
                ->description(__p('user::admin.enable_auto_login_after_registration_desc')),
            Builder::switch('user.signup_repeat_password')
                ->label(__p('user::admin.signup_repeat_password_label'))
                ->description(__p('user::admin.signup_repeat_password_desc')),

            // Builder::switch('user.multi_step_registration_form')
            //     ->label('Multi-step Registration Form')
            //     ->description('Enabling this option will turn the registration process into multiple steps and using as few fields as we can on the first step to entice users to register..'),
            // Builder::switch('user.profile_use_id')
            //     ->label('Profile User ID Connection')
            //     ->description('Set to Yes if you would like to have user profiles connected via their user ID#. Set to No if you would like to have user profiles connected via their user name. Note if you connect via their user ID# you will allow your members the ability to use non-supported characters which are not allowed if connecting a profile with their user name. Warning: This action cannot be reversed.This setting may lock users out if you force log in by their user names'),
            Builder::switch('user.verify_email_at_signup')
                ->label(__p('user::admin.verify_email_at_signup_label'))
                ->description(__p('user::admin.verify_email_at_signup_desc')),
            Builder::text('user.verification_timeout')
                ->label(__p('user::admin.verify_email_phone_number_timeout_label'))
                ->description(__p('user::admin.verify_email_phone_number_timeout_desc'))
                ->required()
                ->asNumber()
                ->yup(Yup::number()
                    ->required()
                    ->positive()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))),
            Builder::text('user.redirect_after_signup')
                ->label(__p('user::admin.redirect_after_signup_label'))
                ->description(__p('user::admin.redirect_after_signup_desc')),
            Builder::text('user.resend_verification_delay_time')
                ->label(__p('user::admin.resend_verification_email_phone_number_delay_time_label'))
                ->description(__p('user::admin.resend_verification_email_phone_number_delay_time_desc'))
                ->required()
                ->asNumber()
                ->yup(Yup::number()
                    ->required()
                    ->int()
                    ->min(1)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))),
            Builder::text('user.days_for_delete_pending_user_verification')
                ->label(__p('user::admin.days_for_delete_pending_user_verification_label'))
                ->description(__p('user::admin.days_for_delete_pending_user_verification_desc'))
                ->required()
                ->asNumber()
                ->yup(Yup::number()
                    ->required()
                    ->positive()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))),
            Builder::switch('user.approve_users')
                ->label(__p('user::admin.approve_users_label'))
                ->description(__p('user::admin.approve_users_desc')),
            Builder::switch('user.force_user_to_upload_on_sign_up')
                ->label(__p('user::admin.force_user_to_upload_on_sign_up_label'))
                ->description(__p('user::admin.force_user_to_upload_on_sign_up_desc')),
            Builder::switch('user.force_user_to_reenter_email')
                ->label(__p('user::admin.force_user_to_reenter_email_or_number_phone_label'))
                ->description(__p('user::admin.force_user_to_reenter_email_or_number_phone_desc')),
        );

        if (app_active('metafox/friend')) {
            $basic->addFields(
                Builder::choice('user.on_signup_new_friend')
                    ->multiple()
                    ->label(__p('user::admin.on_signup_new_friend_label'))
                    ->description(__p('user::admin.on_signup_new_friend_desc'))
                    ->options($this->getAdminAndStaffOptions())
                    ->valueType('array'),
            );
        }

        $basic->addFields(
            Builder::choice('user.on_register_privacy_setting')
                ->label(__p('user::admin.on_register_privacy_setting_label'))
                ->description(__p('user::admin.on_register_privacy_setting_desc'))
                ->options($this->getRegisterPrivacyOptions()),
            Builder::choice('user.available_name_field_on_sign_up')
                ->label(__p('user::admin.display_full_name_and_username_on_sign_up'))
                ->options($this->getDisplayOnSignupOptions())
                ->required()
                ->yup(Yup::string()->required()),
            //             Builder::switch('user.invite_only_community')
            //                 ->label('Invite Only')
            //                 ->description('Enable this option if your community is an "invite only" community.'),
            Builder::switch('user.enable_opt_in_agreement')
                ->label(__p('user::admin.enable_opt_in_agreement_label'))
                ->description(__p('user::admin.enable_opt_in_agreement_desc')),
            Builder::switch('user.new_user_terms_confirmation')
                ->label(__p('user::admin.new_user_terms_confirmation_label'))
                ->description(__p('user::admin.new_user_terms_confirmation_desc')),
            Builder::choice('user.on_register_user_group')
                ->label(__p('user::admin.on_register_role_label'))
                ->description(__p('user::admin.on_register_role_desc'))
                ->required()
                ->options($this->getRoleOptions())
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::switch('user.enable_phone_number_registration')
                ->label(__p('user::admin.enable_phone_number_registration_label'))
                ->description(__p('user::admin.enable_phone_number_registration_desc')),
            $this->buildUsingSmsServiceField(),
            Builder::switch('user.shorter_reset_password_routine')
                ->label(__p('user::admin.shorter_reset_password_routine_label'))
                ->description(__p('user::admin.shorter_reset_password_routine_desc')),
        );

        $this->addDefaultFooter(true);
    }

    protected function getRoleOptions(): array
    {
        $roles = resolve(RoleRepositoryInterface::class)->getRoleOptions();

        $disallowedRoleIds = [
            UserRole::SUPER_ADMIN_USER,
            UserRole::PAGE_USER,
            UserRole::GUEST_USER,
            UserRole::BANNED_USER,
        ];

        $roles = array_filter($roles, function ($role) use ($disallowedRoleIds) {
            return !in_array($role['value'], $disallowedRoleIds);
        });

        return $roles;
    }

    private function getAdminAndStaffOptions(): array
    {
        $listAdminStaff = resolve(UserRepositoryInterface::class)->getAdminAndStaffOptions();

        if (empty($listAdminStaff)) {
            return [
                ['label' => __p('core::phrase.none'), 'value' => 0],
            ];
        }

        return $listAdminStaff;
    }

    public function validated(Request $request): array
    {
        $data = $request->all();

        $roleOptions = array_column($this->getRoleOptions(), 'value');

        $rules = [
            'user.on_register_user_group' => ['required', 'numeric', 'in:' . implode(',', $roleOptions)],
        ];

        $validator = Validator::make($data, $rules);

        $validator->validate();

        return $data;
    }

    protected function getDisplayOnSignupOptions(): array
    {
        return [
            ['value' => UserSupport::DISPLAY_BOTH, 'label' => __p('user::phrase.both')],
            ['value' => UserSupport::DISPLAY_FULL_NAME, 'label' => __p('user::phrase.display_name')],
            ['value' => UserSupport::DISPLAY_USER_NAME, 'label' => __p('user::phrase.username')],
        ];
    }

    protected function getRegisterPrivacyOptions(): array
    {
        $phrase = MetaFoxPrivacy::getUserPrivacy();

        return [
            [
                'value' => MetaFoxPrivacy::EVERYONE,
                'label' => __p($phrase[MetaFoxPrivacy::EVERYONE]),
            ], [
                'value' => MetaFoxPrivacy::MEMBERS,
                'label' => __p($phrase[MetaFoxPrivacy::MEMBERS]),
            ], [
                'value' => MetaFoxPrivacy::FRIENDS,
                'label' => __p($phrase[MetaFoxPrivacy::FRIENDS]),
            ], [
                'value' => MetaFoxPrivacy::ONLY_ME,
                'label' => __p($phrase[MetaFoxPrivacy::ONLY_ME]),
            ],
        ];
    }

    protected function buildUsingSmsServiceField(): AbstractField
    {
        $field = Builder::switch('user.enable_sms_registration')
            ->label(__p('user::admin.enable_sms_registration_label'))
            ->description(__p('user::admin.enable_sms_registration_desc'))
            ->enableWhen([
                'truthy', 'user.enable_phone_number_registration',
            ]);

        $smsService = Settings::get('sms.default');
        if (empty($smsService) || $smsService == 'log') {
            $field->warning(__p('user::admin.enable_sms_registration_confirm_desc'));
        }

        return $field;
    }
}
