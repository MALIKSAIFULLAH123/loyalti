<?php

namespace MetaFox\Notification\Listeners;

use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class GetNotificationSettingsByChannelListener.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class GetNotificationSettingsByChannelListener
{
    /**
     * @param  User|null         $user
     * @param  string            $channel
     * @return array<int, mixed>
     */
    public function handle(?User $user, string $channel): array
    {
        return $this->typeRepository()->getNotificationSettingsByChannel($user, $channel);
    }

    protected function typeRepository(): TypeRepositoryInterface
    {
        return resolve(TypeRepositoryInterface::class);
    }
}
