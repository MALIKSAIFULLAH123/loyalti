<?php

namespace MetaFox\User\Listeners;

use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\UserPasswordHistoryRepositoryInterface;

class ModelPendingActionListener
{
    public function __construct(public UserPasswordHistoryRepositoryInterface $userPasswordHistoryRepository)
    {
    }

    public function handle($model)
    {
        if (!$model instanceof User) {
            return;
        }

        if (!$this->needToChangePassword($model)) {
            return;
        }

        return [
            'id'             => 'user_frequent_change_password',
            'title'          => __p('user::phrase.frequent_check_password_change'),
            'description'    => __p('user::phrase.frequent_check_password_change_desc'),
            'primary_action' => [
                'label'  => __p('user::phrase.change_password'),
                'action' => [
                    'type'    => 'navigate',
                    'payload' => [
                        'url'    => url_utility()->makeApiFullUrl('/user/password/update'),
                        'target' => '_blank',
                    ],
                ],
            ],
            'extra' => [
                'can_close' => false,
            ],
            'skipDismiss' => true,
        ];
    }

    private function needToChangePassword($model): bool
    {
        if (user()->hasAdminRole()) {
            return false;
        }

        if (!Settings::get('user.force_frequent_password_change')) {
            return false;
        }

        $timePeriod = Settings::get('user.force_frequent_password_change_period');

        $latestPassword = $this->userPasswordHistoryRepository->getLatestPassword($model->entityId());

        if (null === $latestPassword) {
            return false;
        }

        $date           = $latestPassword->created_at;

        if (Carbon::now()->diffInDays($date) > $timePeriod) {
            return true;
        }

        return false;
    }
}
