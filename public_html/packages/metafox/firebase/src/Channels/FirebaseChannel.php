<?php

namespace MetaFox\Firebase\Channels;

use MetaFox\Firebase\Contracts\FirebaseChannelInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

abstract class FirebaseChannel
{
    private FirebaseChannelInterface $channel;

    public function __construct(array $params = [])
    {
        $this->setChannel(resolve($this->getChannelClass(), $params));
    }

    abstract public function getChannelClass(): string;

    /**
     * @return FirebaseChannelInterface
     */
    public function getChannel(): FirebaseChannelInterface
    {
        return $this->channel;
    }

    /**
     * @param  FirebaseChannelInterface $channel
     * @return void
     */
    public function setChannel(FirebaseChannelInterface $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * Send the given notification.
     *
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     *
     * @return void
     */
    public function send(IsNotifiable $notifiable, Notification $notification): void
    {
        $this->getChannel()->send($notifiable, $notification);
    }
}
