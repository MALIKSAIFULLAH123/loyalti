<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Request;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class RequestFactory.
 * @method Request create($attributes = [], ?Model $parent = null)
 */
class RequestFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Request::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status_id' => Request::STATUS_PENDING,
        ];
    }

    /**
     * @param User $group
     *
     * @return self
     */
    public function setOwner(User $group): self
    {
        return $this->state(function () use ($group) {
            return [
                'group_id' => $group->entityId(),
            ];
        });
    }
}

// end
