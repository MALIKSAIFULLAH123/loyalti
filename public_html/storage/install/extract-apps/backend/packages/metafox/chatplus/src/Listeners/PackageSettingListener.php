<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * Class PackageSettingListener.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getActivityTypes(): array
    {
        return [];
    }

    public function getEvents(): array
    {
        return [
            'site_settings.updated' => [
                SiteSettingUpdated::class,
            ],
            'user.permissions.extra' => [
                UserExtraPermissionListener::class,
            ],
            'user.role.created' => [
                RoleCreatedListener::class,
            ],
            'user.role.deleted' => [
                RoleDeletedListener::class,
            ],
            'models.notify.updated' => [
                ModelUpdatedListener::class,
            ],
            'models.notify.created' => [
                ModelCreatedListener::class,
            ],
            'models.notify.deleted' => [
                ModelDeletedListener::class,
            ],
            'packages.installed' => [
                PackageInstalledListener::class,
            ],
            'user.unblocked' => [
                UnBlockUserListener::class,
            ],
            'user.blocked' => [
                BlockUserListener::class,
            ],
            'user.role.downgrade' => [
                UserRoleDowngrade::class,
            ],
            'storage.asset.uploaded' => [
                AssetUpdatedListener::class,
            ],
            'storage.asset.reverted' => [
                AssetUpdatedListener::class,
            ],
            'firebase.device_tokens.add' => [
                AddDeviceTokensListener::class,
            ],
            'firebase.device_tokens.remove' => [
                RemoveDeviceTokensListener::class,
            ],
            'chatplus.message.active' => [
                ChatplusActiveListener::class,
            ],
            'like.delete_or_move_reaction' => [
                DeleteOrMoveReactionListener::class,
            ],
            'health-check.checker' => [
                HealthExtraCheckerListener::class,
            ],
            'packages.activated' => [
                PackageActivatedListener::class,
            ],
            'chatplus.job.on_import_conversation' => [
                OnImportConversationListener::class,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'server' => [
                'value'       => '',
                'config_name' => 'chatplus.server',
                'env_var'     => 'MFOX_CHATPLUS_SERVER',
            ],
            'private_code' => [
                'value'       => '',
                'config_name' => 'chatplus.private_code',
                'is_public'   => 0,
                'env_var'     => 'MFOX_CHATPLUS_PRIVATE_CODE',
            ],
            'chat_visibility'   => ['value' => 'public'],
            'enable_video_chat' => ['value' => true],
            'ios_bundle_id'     => ['value' => ''],
            'ios_apn_key'       => [
                'value'       => '',
                'config_name' => 'chatplus.ios_apn_key',
                'is_public'   => 0,
                'env_var'     => 'MFOX_CHATPLUS_IOS_APN_KEY',
            ],
            'ios_apn_key_id' => [
                'value'       => '',
                'config_name' => 'chatplus.ios_apn_key_id',
                'is_public'   => 0,
                'env_var'     => 'MFOX_CHATPLUS_IOS_APN_KEY_ID',
            ],
            'ios_apn_team_id' => [
                'config_name' => config('chatplus.ios_apn_team_id'),
                'value'       => '',
                'is_public'   => 0,
                'env_var'     => 'MFOX_CHATPLUS_IOS_APN_TEAM_ID',
            ],
            'enable_voice_call'                 => ['value' => true],
            'enable_favorite_rooms'             => ['value' => true],
            'enable_edit_message'               => ['value' => true],
            'enable_delete_message'             => ['value' => true],
            'enable_star_message'               => ['value' => true],
            'enable_pin_message'                => ['value' => true],
            'minimise_chat'                     => ['value' => true],
            'enable_discussion'                 => ['value' => true],
            'message_blocked_edit_in_minutes'   => ['value' => 0],
            'message_blocked_delete_in_minutes' => ['value' => 0],
            'enable_thread'                     => ['value' => false],
            'jitsi_enable_auth'                 => [
                'value'       => false,
                'config_name' => 'chatplus.jitsi_enable_auth',
                'env_var'     => 'MFOX_CHATPLUS_JITSI_ENABLE_AUTH',
            ],
            'jitsi_domain_option' => [
                'config_name' => 'chatplus.jitsi_domain_option',
                'value'       => 'metafox',
                'env_var'     => 'MFOX_CHATPLUS_JITSI_DOMAIN_OPTION',
            ],
            'jitsi_domain' => [
                'config_name' => 'chatplus.jitsi_domain',
                'value'       => '',
                'env_var'     => 'MFOX_CHATPLUS_JITSI_DOMAIN',
            ],
            'jitsi_application_id' => [
                'config_name' => 'chatplus.jitsi_application_id',
                'value'       => '',
                'env_var'     => 'MFOX_CHATPLUS_JITSI_APPLICATION_ID',
            ],
            'jitsi_application_secret' => [
                'config_name' => 'chatplus.jitsi_application_secret',
                'value'       => '',
                'is_public'   => 0,
                'env_var'     => 'MFOX_CHATPLUS_JITSI_APPLICATION_SECRET',
            ],
            'user_per_call_limit' => ['value' => 20],
            'call_limit'          => ['value' => 20],
            'firebase_server_key' => [
                'config_name' => 'chatplus.firebase_server_key',
                'value'       => '',
                'is_public'   => 0,
            ],
            'firebase_sender_id' => [
                'config_name' => 'chatplus.firebase_sender_id',
                'value'       => '',
                'is_public'   => 0,
            ],
            'firebase_project_id' => [
                'config_name' => 'chatplus.firebase_project_id',
                'value'       => '',
                'is_public'   => 0,
            ],
        ];
    }
}
