<?php

namespace MetaFox\Invite\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'invite';
        $vars   = [
            'invite.enable_check_duplicate_invite',
            'invite.invite_link_expire_days',
            'invite.make_invited_users_friends_with_their_host',
            'invite.invite_only',
            'invite.show_invite_code_on_signup',
            'invite.auto_approve_user_registered',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::admin.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('invite.invite_link_expire_days')
                ->label(__p('invite::admin.invite_link_expire_days_label'))
                ->description(__p('invite::admin.invite_link_expire_days_desc'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->required()
                        ->unint()
                        ->min(0)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::switch('invite.enable_check_duplicate_invite')
                ->label(__p('invite::admin.enable_check_duplicate_invite_label'))
                ->description(__p('invite::admin.enable_check_duplicate_invite_desc')),
            Builder::switch('invite.make_invited_users_friends_with_their_host')
                ->label(__p('invite::admin.make_invited_users_friends_with_their_host_label'))
                ->description(__p('invite::admin.make_invited_users_friends_with_their_host_desc')),
            Builder::switch('invite.invite_only')
                ->label(__p('invite::admin.invite_only_label'))
                ->description(__p('invite::admin.invite_only_desc')),
            Builder::switch('invite.show_invite_code_on_signup')
                ->label(__p('invite::admin.show_invite_code_on_signup_form_label'))
                ->description(__p('invite::admin.show_invite_code_on_signup_form_desc'))
                ->showWhen(['falsy', 'invite.invite_only']),
            Builder::switch('invite.auto_approve_user_registered')
                ->label(__p('invite::admin.auto_approve_user_registered_label'))
                ->description(__p('invite::admin.auto_approve_user_registered_desc')),
        );

        $this->addDefaultFooter(true);
    }
}
