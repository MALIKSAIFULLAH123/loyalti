<?php

namespace MetaFox\SEO\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\SEO\Models\Meta;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class MetaFactory.
 * @method Meta create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class MetaFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Meta::class;

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
