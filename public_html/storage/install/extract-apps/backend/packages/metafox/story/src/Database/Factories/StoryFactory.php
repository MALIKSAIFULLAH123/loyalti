<?php

namespace MetaFox\Story\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Story\Models\Story;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class StoryFactory
 * @method Story create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class StoryFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Story::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}

// end
