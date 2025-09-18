<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Invite\Jobs\RemoveExpiredInvitationCodeJob;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Policies\InvitePolicy;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;

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
    public function getSiteSettings(): array
    {
        return [
            'enable_check_duplicate_invite'              => ['value' => true],
            'invite_link_expire_days'                    => ['value' => 7],
            'make_invited_users_friends_with_their_host' => ['value' => true],
            'invite_only'                                => ['value' => false],
            'show_invite_code_on_signup'                 => ['value' => true],
            'auto_approve_user_registered'               => ['value' => false],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            Invite::ENTITY_TYPE => [
                'must_wait_minutes_until_are_allowed' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 60,
                    ],
                ],
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'parseRoute'                                  => [
                InviteRouteListener::class,
            ],
            'invite.user_register_update_status'          => [
                UpdatedInviteListener::class,
            ],
            'models.notify.deleted'                       => [
                ModelDeletedListener::class,
            ],
            'models.notify.created'                       => [
                ModelCreatedListener::class,
            ],
            'user.registration.extra_field.rules'         => [
                InviteRegistrationFieldRulesListener::class,
            ],
            'user.registration.extra_field.rule_messages' => [
                InviteRegistrationFieldRuleMessagesListener::class,
            ],
            'user.registration.extra_fields.build'        => [
                InviteRegistrationFieldsListener::class,
            ],
            'user.registration.invite_code_field.build'   => [
                InviteRegistrationFieldsListener::class,
            ],
            'invite.get_user_invite_code'                 => [
                GetUserInviteCodeListener::class,
            ],
            'invite.request_rule.build'                   => [
                BuildRequestRuleListener::class,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Invite::class => InvitePolicy::class,
        ];
    }

    public function getActivityPointSettings(): array
    {
        return [
            'metafox/invite' => [
                [
                    'name'               => Invite::ENTITY_TYPE . '.completed',
                    'action'             => 'completed',
                    'module_id'          => 'invite',
                    'package_id'         => 'metafox/invite',
                    'description_phrase' => 'invite::phrase.setting_invite_completed_description',
                ],
                [
                    'name'               => Invite::ENTITY_TYPE . '.create',
                    'action'             => 'create',
                    'module_id'          => 'invite',
                    'package_id'         => 'metafox/invite',
                    'description_phrase' => 'invite::phrase.setting_invite_create_description',
                ],
            ],
        ];
    }

    public function getActivityPointActions(): array
    {
        return [
            'metafox/invite' => [
                [
                    'name'         => Invite::ENTITY_TYPE . '.completed',
                    'package_id'   => 'metafox/invite',
                    'label_phrase' => 'invite::activitypoint.action_type_invite_completed_label',
                ],
                [
                    'name'         => Invite::ENTITY_TYPE . '.create',
                    'package_id'   => 'metafox/invite',
                    'label_phrase' => 'invite::activitypoint.action_type_invite_create_label',
                ],
            ],
        ];
    }

    /**
     * @param Schedule $schedule
     * @return void
     */
    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(RemoveExpiredInvitationCodeJob::class)->hourly()->withoutOverlapping();
    }

    public function getUserPermissions(): array
    {

        return [
            Invite::ENTITY_TYPE => [
                'create' => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }
}
