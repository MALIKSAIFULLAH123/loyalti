<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\ExampleRule;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class ExampleRuleFactory.
 * @method ExampleRule create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class ExampleRuleFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExampleRule::class;

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
