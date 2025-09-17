<?php

namespace MetaFox\Queue\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Queue\Models\FailedJob;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class FailedJobFactory.
 * @method FailedJob create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class FailedJobFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FailedJob::class;

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
