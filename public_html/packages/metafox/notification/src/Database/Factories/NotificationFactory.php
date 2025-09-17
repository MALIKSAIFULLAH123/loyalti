<?php

namespace MetaFox\Notification\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Notification\Models\Notification;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Notifications\Notification as NotificationContract;

/**
 * Class NotificationFactory.
 *
 * @method Notification create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'    => 1,
            'user_type'  => 'user',
            'item_id'    => 1,
            'item_type'  => 'user',
            'type'       => 'friend_request',
            'is_request' => false,
        ];
    }

    /**
     * @param User $user
     *
     * @return NotificationFactory
     */
    public function setNotifiable(User $user): self
    {
        return $this->state(function () use ($user) {
            return [
                'notifiable_id'   => $user->entityId(),
                'notifiable_type' => $user->entityType(),
            ];
        });
    }

    /**
     * @param  NotificationContract $notification
     * @param  IsNotifiable         $notifiable
     * @return NotificationFactory
     */
    public function setData(NotificationContract $notification, IsNotifiable $notifiable): self
    {
        return $this->state(function () use ($notification, $notifiable) {
            return [
                'data' => [
                    'class' => get_class($notification),
                    'data'  => $notification->toArray($notifiable),
                ],
            ];
        });
    }
}

// end
