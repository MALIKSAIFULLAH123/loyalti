<?php

namespace MetaFox\Activity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Type;

/**
 * Class TypeFactory.
 * @codeCoverageIgnore
 * @ignore
 */
class TypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Type::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type'         => $this->faker->sentence(5),
            'module_id'    => 'test',
            'is_active'    => true,
            'title'        => $this->faker->sentence(5),
            'description'  => $this->faker->sentence(5),
            'is_system'    => 0,
            'value_actual' => [
                'can_comment'     => true,
                'can_like'        => true,
                'can_share'       => true,
                'can_edit'        => true,
                'can_create_feed' => true,
                'action_on_feed'  => false,
            ],
        ];
    }

    public function setAbility($array)
    {
        return $this->state(fn () => ['value_actual' => $array]);
    }

    /**
     * Clear cache.
     *
     * @return TypeFactory
     */
    public function seed()
    {
        return $this->afterCreating(function () {
            resolve(TypeManager::class)->refresh();
        });
    }
}
