<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\MultiFactorToken;

/**
 * Class MultiFactorTokenFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 * @method MultiFactorToken create($attributes = [], ?Model $parent = null)
 */
class MultiFactorTokenFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MultiFactorToken::class;

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
