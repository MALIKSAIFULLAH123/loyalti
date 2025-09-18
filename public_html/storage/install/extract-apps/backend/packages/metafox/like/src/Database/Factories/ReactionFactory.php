<?php

namespace MetaFox\Like\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Like\Models\Reaction;

/**
 * Class ReactionFactory.
 * @method Reaction create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class ReactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'      => uniqid('reaction factory '),
            'is_active'  => 1,
            'icon_path'  => $this->faker->url,
            'color'      => '#fff',
            'server_id'  => 'public',
            'ordering'   => 1,
            'is_default' => 0,
        ];
    }
}
