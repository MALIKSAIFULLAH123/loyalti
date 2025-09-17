<?php

namespace MetaFox\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Layout\Models\Snippet;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class SnippetFactory.
 * @method Snippet create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class SnippetFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Snippet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'  => $this->faker->name,
            'type'  => 'snippet',
            'data'  => '',
            'theme' => 'paper',
        ];
    }
}

// end
