<?php

namespace MetaFox\User\Listeners;

use Carbon\Carbon;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class CollectTotalItemsStatListener extends AbstractClass
{
    /**
     * @param Carbon|null $after
     * @param Carbon|null $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        if ($after) {
            return [
                [
                    'name'  => User::ENTITY_TYPE,
                    'label' => 'user::phrase.user_stat_label',
                    'value' => resolve(UserRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
                ],
                [
                    'name'  => 'online_user',
                    'label' => 'user::phrase.online_user_stat_label',
                    'value' => resolve(UserRepositoryInterface::class)->getOnlineUserForStat($after, $before),
                ],
            ];
        }

        return [
            [
                'name'  => User::ENTITY_TYPE,
                'label' => 'user::phrase.user_stat_label',
                'value' => resolve(UserRepositoryInterface::class)->getTotalItemByPeriod(),
            ],
        ];
    }

    public function getSiteStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => 'online_user',
                'label' => 'user::phrase.online_user_stat_label',
                'value' => resolve(UserRepositoryInterface::class)->getOnlineUserCount(),
                'group' => 'site_stat',
            ],
            [
                'name'  => 'pending_user',
                'label' => 'user::phrase.pending_user_stat_label',
                'value' => resolve(UserRepositoryInterface::class)->getPendingUserCount(),
                'group' => 'site_stat',
            ],
        ];
    }
}
