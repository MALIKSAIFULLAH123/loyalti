<?php

namespace MetaFox\Report\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Report\Models\ReportItem;

/**
 * Class ReportItemFactory.
 * @method ReportItem create($attributes = [], ?Model $parent = null)
 */
class ReportItemFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReportItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feedback' => $this->faker->text,
        ];
    }

    public function setReasonId(int $id): self
    {
        return $this->state(function () use ($id) {
            return [
                'reason_id' => $id,
            ];
        });
    }
}
