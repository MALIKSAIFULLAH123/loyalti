<?php

namespace MetaFox\Layout\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Layout\Models\Revision;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class RevisionFactory
 * @method Revision create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class RevisionFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Revision::class;

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
