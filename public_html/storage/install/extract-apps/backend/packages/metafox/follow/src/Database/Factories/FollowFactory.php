<?php

namespace MetaFox\Follow\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Follow\Models\Follow;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class FollowFactory.
 * @method Follow create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class FollowFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Follow::class;

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
