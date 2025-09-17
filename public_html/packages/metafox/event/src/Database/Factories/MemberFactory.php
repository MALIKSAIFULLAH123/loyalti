<?php

namespace MetaFox\Event\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

class MemberFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id'   => Member::ROLE_MEMBER,
            'rsvp_id'   => fake()->randomElement([0, 0, 1, 2, 3]),
            'user_id'   => $this->randomUserId(),
            'user_type' => 'user',
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
     * @param int $rsvp
     *
     * @return self
     */
    public function setRsvp(int $rsvp)
    {
        return $this->state(function () use ($rsvp) {
            return [
                'rsvp_id' => $rsvp,
            ];
        });
    }

    /**
     * @param int $role
     *
     * @return self
     */
    public function setRole(int $role): self
    {
        return $this->state(function () use ($role) {
            return [
                'role_id' => $role,
            ];
        });
    }
}
