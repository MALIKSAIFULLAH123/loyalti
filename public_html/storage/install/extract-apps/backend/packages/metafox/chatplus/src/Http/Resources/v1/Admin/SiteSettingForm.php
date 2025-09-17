<?php

namespace MetaFox\ChatPlus\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'chatplus';
        $vars   = [
            'chatplus.server',
            'chatplus.private_code',
            'chatplus.chat_visibility',
            'chatplus.ios_bundle_id',
            'chatplus.ios_apn_key',
            'chatplus.ios_apn_key_id',
            'chatplus.ios_apn_team_id',
            'chatplus.enable_video_chat',
            'chatplus.enable_voice_call',
            'chatplus.enable_favorite_rooms',
            'chatplus.enable_edit_message',
            'chatplus.enable_delete_message',
            'chatplus.enable_star_message',
            'chatplus.enable_pin_message',
            'chatplus.minimise_chat',
            'chatplus.enable_discussion',
            'chatplus.message_blocked_edit_in_minutes',
            'chatplus.message_blocked_delete_in_minutes',
            'chatplus.enable_thread',
            'chatplus.jitsi_enable_auth',
            'chatplus.jitsi_domain_option',
            'chatplus.jitsi_domain',
            'chatplus.jitsi_application_id',
            'chatplus.jitsi_application_secret',
            'firebase.server_key',
            'firebase.sender_id',
            'firebase.project_id',
            'chatplus.user_per_call_limit',
            'chatplus.call_limit',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::text('chatplus.server')
                ->required()
                ->returnKeyType('next')
                ->variant('outlined')
                ->label(__p('chatplus::phrase.server'))
                ->yup(
                    Yup::string()
                    ->format('url')
                    ->required(__p('chatplus::validation.server_is_a_required_field'))
                )
        );

        $basic->addField(
            Builder::text('chatplus.private_code')
                ->required()
                ->returnKeyType('next')
                ->variant('outlined')
                ->yup(Yup::string()->required(__p('validation.this_field_is_a_required_field')))
                ->label(__p('chatplus::phrase.private_code'))
        );

        $basic->addField(
            Builder::choice('chatplus.chat_visibility')
                ->required()
                ->returnKeyType('next')
                ->defaultValue('public')
                ->label(__p('chatplus::phrase.visibility'))
                ->options(
                    [
                        ['value' => 'friendship', 'label' => 'Friends Only'], ['value' => 'public', 'label' => 'All Members'],
                    ]
                )
                ->yup(Yup::string()->required(__p('chatplus::validation.visibility_is_a_required_field')))
        );

        $basic->addField(
            Builder::choice('chatplus.jitsi_domain_option')
                ->required()
                ->returnKeyType('next')
                ->defaultValue('metafox')
                ->label(__p('chatplus::phrase.select_video_bridge_service'))
                ->options([
                    ['value' => 'metafox', 'label' => __p('chatplus::phrase.metafox_video_bridge_service')],
                    ['value' => 'custom', 'label' => __p('chatplus::phrase.build_yourself_video_service')],
                ])
                ->yup(Yup::string()->required(__p('chatplus::validation.video_bridge_service_is_a_required_field')))
        );
        $basic->addField(
            Builder::text('chatplus.jitsi_domain')
                ->returnKeyType('next')
                ->variant('outlined')
                ->showWhen(['eq', 'chatplus.jitsi_domain_option', 'custom'])
                ->requiredWhen(['eq', 'chatplus.jitsi_domain_option', 'custom'])
                ->label(__p('chatplus::phrase.jitsi_domain'))
                ->yup(
                    Yup::string()
                    ->when(
                        Yup::when('jitsi_domain_option')
                            ->is('custom')
                            ->then(
                                Yup::string()
                                    ->required(__p('chatplus::validation.video_bridge_domain_is_a_required_field'))
                            )
                    )
                )
        );
        $basic->addField(
            Builder::checkbox('chatplus.jitsi_enable_auth')
                ->label(__p('chatplus::phrase.jitsi_enable_jwt_auth'))
                ->variant('outlined')
                ->showWhen(['eq', 'chatplus.jitsi_domain_option', 'custom'])
                ->requiredWhen(['eq', 'chatplus.jitsi_domain_option', 'custom'])
        );
        $basic->addField(
            Builder::text('chatplus.jitsi_application_id')
                ->returnKeyType('next')
                ->variant('outlined')
                ->showWhen(['eq', 'chatplus.jitsi_domain_option', 'custom'])
                ->requiredWhen(['eq', 'chatplus.jitsi_domain_option', 'custom'])
                ->label(__p('chatplus::phrase.jitsi_application_id'))
                ->yup(
                    Yup::string()
                    ->when(
                        Yup::when('jitsi_domain_option')
                            ->is('custom')
                            ->then(
                                Yup::string()
                                    ->required(__p('chatplus::validation.video_bridge_application_id_is_a_required_field'))
                            )
                    )
                )
        );
        $basic->addField(
            Builder::text('chatplus.jitsi_application_secret')
                ->returnKeyType('next')
                ->variant('outlined')
                ->showWhen(['eq', 'chatplus.jitsi_domain_option', 'custom'])
                ->requiredWhen(['eq', 'chatplus.jitsi_domain_option', 'custom'])
                ->label(__p('chatplus::phrase.jitsi_application_secret'))
                ->yup(
                    Yup::string()
                    ->when(
                        Yup::when('jitsi_domain_option')
                            ->is('custom')
                            ->then(
                                Yup::string()
                                    ->required(__p('chatplus::validation.video_bridge_application_secret_is_a_required_field'))
                            )
                    )
                )
        );
        if (app_active('metafox/firebase')) {
            $basic->addField(
                Builder::text('firebase.server_key')
                    ->returnKeyType('next')
                    ->variant('outlined')
                    ->disabled()
                    ->description(__p('chatplus::phrase.firebase_setting_description'))
                    ->label(__p('chatplus::phrase.firebase_server_key'))
            );
            $basic->addField(
                Builder::text('firebase.sender_id')
                    ->variant('outlined')
                    ->disabled()
                    ->description(__p('chatplus::phrase.firebase_setting_description'))
                    ->label(__p('chatplus::phrase.firebase_sender_id'))
            );
            $basic->addField(
                Builder::text('firebase.project_id')
                    ->variant('outlined')
                    ->disabled()
                    ->description(__p('chatplus::phrase.firebase_setting_description'))
                    ->label(__p('chatplus::phrase.firebase_project_id'))
            );
            $basic->addField(
                Builder::linkButton('chatplus.go_to_firebase_setting')
                    ->link('/firebase/setting')
                    ->variant('link')
                    ->sizeNormal()
                    ->color('primary')
                    ->setAttribute('controlProps', ['sx' => ['display' => 'block']])
                    ->label(__p('chatplus::phrase.go_to_firebase_settings'))
            );
        }

        $basic->addField(
            Builder::text('chatplus.ios_bundle_id')
                ->label(__p('chatplus::phrase.ios_bundle_id'))
                ->yup(Yup::string()->optional())
        );

        $basic->addField(
            Builder::text('chatplus.ios_apn_team_id')
                ->label(__p('chatplus::phrase.ios_apn_team_id'))
                ->yup(Yup::string()->optional())
        );

        $basic->addField(
            Builder::text('chatplus.ios_apn_key_id')
                ->label(__p('chatplus::phrase.ios_apn_key_id'))
                ->yup(Yup::string()->optional())
        );

        $basic->addField(
            Builder::textArea('chatplus.ios_apn_key')
                ->label(__p('chatplus::phrase.ios_apn_key'))
                ->yup(Yup::string()->optional())
        );

        $enableFeatures = $this->addSection([
            'name'  => 'enable_features',
            'label' => __p('chatplus::phrase.enable_features'),
        ]);
        $enableFeatures->addField(
            Builder::checkbox('chatplus.enable_video_chat')
                ->label(__p('chatplus::phrase.enable_video_chat'))
        );
        $enableFeatures->addField(
            Builder::checkbox('chatplus.enable_voice_call')
                ->label(__p('chatplus::phrase.enable_voice_call'))
        );
        $enableFeatures->addField(
            Builder::checkbox('chatplus.enable_favorite_rooms')
                ->label(__p('chatplus::phrase.enable_favorite_rooms'))
        );
        $enableFeatures->addField(
            Builder::checkbox('chatplus.enable_edit_message')
                ->label(__p('chatplus::phrase.enable_edit_message'))
        );
        $enableFeatures->addField(
            Builder::checkbox('chatplus.enable_delete_message')
                ->label(__p('chatplus::phrase.enable_delete_message'))
        );
        $enableFeatures->addField(
            Builder::checkbox('chatplus.enable_star_message')
                ->label(__p('chatplus::phrase.enable_star_message'))
        );
        $enableFeatures->addField(
            Builder::checkbox('chatplus.enable_pin_message')
                ->label(__p('chatplus::phrase.enable_pin_message'))
        );

        $this->addDefaultFooter(true);
    }
}
