<?php

namespace MetaFox\Friend\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class FriendListFactory.
 * @method FriendList create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendListFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FriendList::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => $this->faker->name,
            'user_id'   => 1,
            'user_type' => 'user',
        ];
    }

    public function setOwner(User $user)
    {
        return $this;
    }

    public function setItem(Content $item): static
    {
        return $this;
    }
}
