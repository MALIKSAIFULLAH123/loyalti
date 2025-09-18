<?php

namespace MetaFox\Group\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Mute;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class MuteFactory.
 * @method Mute create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class MuteFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mute::class;

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
