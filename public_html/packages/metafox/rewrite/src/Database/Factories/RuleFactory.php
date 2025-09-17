<?php

namespace MetaFox\Rewrite\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Rewrite\Models\Rule;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class RuleFactory.
 * @method Rule create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class RuleFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rule::class;

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
