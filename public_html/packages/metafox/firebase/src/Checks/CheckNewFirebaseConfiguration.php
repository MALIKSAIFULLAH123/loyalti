<?php

namespace MetaFox\Firebase\Checks;

use Carbon\Carbon;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckNewFirebaseConfiguration extends Checker
{
    public function check(): Result
    {
        $settings = [
            'firebase.client_id',
            'firebase.client_email',
            'firebase.private_key',
        ];

        $oldConfigureExitsted = !empty(Settings::get('firebase.server_key'));

        $result = $this->makeResult();

        $success = true;
        foreach ($settings as $setting) {
            $settingValue = Settings::get($setting) ?: null;

            if ($oldConfigureExitsted && (!is_string($settingValue) || empty($settingValue))) {
                $success = false;
                break;
            }
        }

        if (!$success) {
            $actions = $this->getActions();
            $title   = $this->getTitle();
            $message = $this->getMessage();

            if ($this->isPassedDeadline()) {
                $result->error($message, $title, $actions);
            } else {
                $result->warn($message, $title, $actions);
            }

            return $result;
        }

        return $result;
    }

    protected function getActions(): array
    {
        return [
            [
                'name'    => 'more_details',
                'title'   => __p('firebase::phrase.more_details'),
                'action'  => 'navigate',
                'payload' => [
                    'url'    => 'https://firebase.google.com/docs/cloud-messaging/migrate-v1',
                    'target' => '_blank',
                ],
                'config' => [
                    'variant' => 'link',
                    'size'    => 'small',
                    'sx'      => [
                        'height' => 'auto',
                    ],
                ],
            ],
            [
                'name'    => 'view_guideline',
                'title'   => __p('firebase::phrase.how_to_configure'),
                'action'  => 'navigate',
                'payload' => [
                    'url'    => 'https://docs.phpfox.com/display/MFMAN/Set+up+Firebase',
                    'target' => '_blank',
                ],
                'config' => [
                    'variant' => 'link',
                    'size'    => 'small',
                    'sx'      => [
                        'height' => 'auto',
                    ],
                ],
            ],
            [
                'name'    => 'configure_now',
                'title'   => __p('firebase::phrase.configure_now'),
                'action'  => 'navigate',
                'payload' => [
                    'url' => '/firebase/setting/',
                ],
                'config' => [
                    'variant' => 'link',
                    'size'    => 'small',
                    'sx'      => [
                        'height' => 'auto',
                    ],
                ],
            ],
        ];
    }

    public function getName()
    {
        return __CLASS__ . ($this->isPassedDeadline() ? '_error' : '_warning');
    }

    protected function getTitle(): string
    {
        return __p('firebase::phrase.migrate_legacy_firebase_apis_title');
    }

    protected function getMessage(): string
    {
        if ($this->isPassedDeadline()) {
            return __p('firebase::phrase.migrate_legacy_firebase_apis_error_desc');
        }

        return __p('firebase::phrase.migrate_legacy_firebase_apis_warning_desc');
    }

    protected function getDeadLine(): Carbon
    {
        return (new Carbon('June, 2024'))->startOfMonth();
    }

    protected function isPassedDeadline(): bool
    {
        return now()->isAfter($this->getDeadLine());
    }
}
