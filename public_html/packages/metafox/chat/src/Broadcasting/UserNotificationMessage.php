<?php

namespace MetaFox\Chat\Broadcasting;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Chat\Support\Helper;
use MetaFox\Chat\Traits\MessageTraits;

class UserNotificationMessage implements ShouldBroadcast
{
    use SerializesModels;
    use Dispatchable;
    use MessageTraits;

    public bool $afterCommit = true;

    /**
     * @param int    $userId
     * @param string $broadcastType
     */
    public function __construct(private  int $userId, private  string $broadcastType, private string $actionType = '')
    {
    }

    public function broadcastOn()
    {
        return 'user.' . $this->userId;
    }

    /**
     * Event name for client to register.
     * @return string
     */
    public function broadcastAs()
    {
        switch ($this->broadcastType) {
            case Helper::NOTIFICATION_UPDATE:
                $type = 'NotificationUpdate';
                break;
        }

        return $type;
    }

    /**
     * Data to send to client.
     * @return array
     */
    public function broadcastWith(): array
    {
        $userNotification = resolve(SubscriptionRepositoryInterface::class)->getTotalUnseenNotification([
            'user_id'   => $this->userId,
            'user_type' => 'user',
        ]);

        return [
            'module_name'        => 'chat',
            'user_id'            => $this->userId,
            'user_type'          => 'user',
            'total_notification' => !empty($userNotification) ? $userNotification : 0,
            'action_type'        => $this->actionType,
        ];
    }
}
