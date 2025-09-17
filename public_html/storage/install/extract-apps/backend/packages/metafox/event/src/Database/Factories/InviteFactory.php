<?php

namespace MetaFox\Event\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Invite;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

use function Sodium\crypto_box_publickey_from_secretkey;

class InviteFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invite::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id'   => Event::all()->random()->id,
            'user_id'    => 1,
            'user_type'  => 'user',
            'owner_id'   => 2,
            'owner_type' => 2,
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

    public function seed()
    {
        return $this->state(function () {
            /** @var Event $event */
            $event  = Event::all()->random();

            return [
                'event_id'   => $event->id,
                'owner_id'   => $event->ownerId(),
                'owner_type' => $event->ownerType(),
            ];
        });
    }
}
