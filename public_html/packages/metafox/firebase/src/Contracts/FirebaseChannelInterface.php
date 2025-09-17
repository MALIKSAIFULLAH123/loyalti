<?php

namespace MetaFox\Firebase\Contracts;

use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Interface FirebaseChannelInterface.
 */
interface FirebaseChannelInterface
{
    /**
     * @param  IsNotifiable $notifiable
     * @param  Notification $notification
     * @return void
     */
    public function send(IsNotifiable $notifiable, Notification $notification): void;

    /**
     * @return bool
     */
    public function validateConfiguration(): bool;
}
