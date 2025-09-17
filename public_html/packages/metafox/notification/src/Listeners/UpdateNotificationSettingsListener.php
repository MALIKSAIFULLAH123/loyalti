<?php

namespace MetaFox\Notification\Listeners;

use MetaFox\Notification\Contracts\ChannelManagerInterface;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;

/**
 * Class UpdateNotificationSettingsListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateNotificationSettingsListener
{
    public function __construct(
        private TypeRepositoryInterface $typeRepository,
        private ChannelManagerInterface $channelManager,
    ) {
    }
    /**
     * @param User|null         $user
     * @param array<string,int> $attributes
     *
     * @return bool
     */
    public function handle(?User $user, array $attributes): bool
    {
        if (!$user instanceof IsNotifiable) {
            return false;
        }

        $result = $this->typeRepository->updateNotificationSettingsByChannel($user, $attributes);
        if ($result) {
            $this->channelManager->forgetChannelCacheForNotifiable($user);
        }

        return $result;
    }
}
