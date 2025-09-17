<?php

namespace MetaFox\Report\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Report\Models\ReportReason;

/**
 * Class ReportReasonFactory.
 * @method ReportReason create($attributes = [], ?Model $parent = null)
 */
class ReportReasonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReportReason::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => $this->faker->name,
            'ordering' => $this->faker->numberBetween(1, 999),
        ];
    }
}
