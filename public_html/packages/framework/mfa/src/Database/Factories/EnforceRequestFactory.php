<?php

namespace MetaFox\Mfa\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Mfa\Models\EnforceRequest;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class EnforceRequestFactory
 * @method UserEnforce create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class EnforceRequestFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EnforceRequest::class;

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
