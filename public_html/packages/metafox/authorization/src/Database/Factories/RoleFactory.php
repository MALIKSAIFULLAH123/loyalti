<?php

namespace MetaFox\Authorization\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class RoleFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 * @method Role create($attributes = [], ?Model $parent = null)
 */
class RoleFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'       => $this->faker->jobTitle,
            'guard_name' => 'api',
        ];
    }
}

// end
