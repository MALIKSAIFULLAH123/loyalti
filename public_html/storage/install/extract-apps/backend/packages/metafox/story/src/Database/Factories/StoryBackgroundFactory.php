<?php

namespace MetaFox\Story\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Story\Models\StoryBackground;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class StoryBackgroundFactory
 * @method StoryBackground create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class StoryBackgroundFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoryBackground::class;

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
