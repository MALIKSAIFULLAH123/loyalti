<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Mfa\Jobs\DeleteInactiveUserVerifyCodeJob;
use MetaFox\Mfa\Notifications\BruteForceMfaNotification;
use MetaFox\Platform\Support\BasePackageSettingListener;

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
    /**
     * @return array<string, mixed>
     */
    public function getUserValues(): array
    {
        return [];
    }

    public function getEvents(): array
    {
        return [
            'models.notify.updated' => [
                ModelUpdatedListener::class,
            ],
            'models.actions.pending' => [
                ModelPendingActionListener::class,
            ],
            'packages.installed' => [
                PackageInstalledListener::class,
            ],
            'user.deleting' => [
                UserDeletingListener::class,
            ],
            'user.request_mfa_token' => [
                [MfaListener::class, 'requestMfaToken'],
            ],
            'user.validate_password_for_grant' => [
                [MfaListener::class, 'validateForPassportPasswordGrant'],
            ],
            'user.user_mfa_enabled' => [
                [MfaListener::class, 'hasMfaEnabled'],
            ],
            'user.user_has_enabled_service' => [
                [MfaListener::class, 'hasMfaServiceEnabled'],
            ],
            'user.validate_mfa_field_for_request' => [
                [MfaListener::class, 'validateMfaFieldForRequest'],
            ],
            'user.signed_in' => [
                UserSignedInListener::class,
            ],
            'user.validate_status' => [
                UserValidateStatusListener::class,
            ],
            'mfa.service_activated' => [
                MfaServiceActivatedListener::class,
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [];
    }

    public function getSiteSettings(): array
    {
        return [
            'confirm_password'               => ['value' => false],
            'resend_verification_delay_time' => ['value' => 60],
            'verify_code_timeout'            => ['value' => 60],
            'brute_force_attempts_count'     => ['value' => 5],
            'brute_force_cool_down'          => ['value' => 0],
            'enforce_mfa'                    => ['value' => false],
            'enforce_mfa_targets'            => ['value' => 'all'],
            'enforce_mfa_timeout'            => ['value' => 10],
            'enforce_mfa_roles'              => ['value' => []],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'brute_force_notification',
                'module_id'  => 'mfa',
                'title'      => 'mfa::phrase.mfa_failure_notification',
                'handler'    => BruteForceMfaNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['mail', 'sms'],
                'ordering'   => 1,
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(DeleteInactiveUserVerifyCodeJob::class)->cron('0 0 * */2 *');
    }
}
