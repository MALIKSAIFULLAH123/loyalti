<?php

namespace MetaFox\Profile\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Profile\Models\Field;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * class FieldFactory.
 * @method Field create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class FieldFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Field::class;

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
