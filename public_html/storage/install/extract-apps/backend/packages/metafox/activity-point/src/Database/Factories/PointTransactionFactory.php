<?php

namespace MetaFox\ActivityPoint\Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\ActivityPoint\Models\PointTransaction;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class PointTransactionFactory.
 * @method PointTransaction create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class PointTransactionFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PointTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id'     => 'activitypoint',
            'package_id'    => 'metafox/activity-point',
            'type'          => rand(1, 6),
            'action'        => $this->faker->sentence(7),
            'points'        => 10,
            'is_hidden'     => 0,
            'action_params' => [],
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ];
    }
}

// end
