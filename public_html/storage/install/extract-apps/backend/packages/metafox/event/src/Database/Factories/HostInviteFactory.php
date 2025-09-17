<?php

namespace MetaFox\Event\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\HostInvite;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

class HostInviteFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HostInvite::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status_id' => HostInvite::STATUS_PENDING,
        ];
    }

    /**
     * @param Event $event
     *
     * @return self
     */
    public function setEvent(Event $event)
    {
        return $this->state(function () use ($event) {
            return [
                'event_id' => $event->entityId(),
            ];
        });
    }

    /**
     * @param User $event
     *
     * @return self
     */
    public function setUser(User $user)
    {
        return $this->state(function () use ($user) {
            return [
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
            ];
        });
    }

    /**
     * @param User $event
     *
     * @return self
     */
    public function setOwner(User $user)
    {
        return $this->state(function () use ($user) {
            return [
                'owner_id'   => $user->entityId(),
                'owner_type' => $user->entityType(),
            ];
        });
    }
}
