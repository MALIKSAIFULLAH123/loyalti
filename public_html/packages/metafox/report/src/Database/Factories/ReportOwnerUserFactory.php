<?php

namespace MetaFox\Report\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Report\Models\ReportOwnerUser;

/**
 * @method ReportOwnerUser create($attributes = [], ?Model $parent = null)
 */
class ReportOwnerUserFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReportOwnerUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reason_id'  => null,
            'ip_address' => $this->faker->ipv4,
            'feedback'   => $this->faker->text,
        ];
    }

    /**
     * @param int $reportId
     *
     * @return self
     */
    public function setReportId(int $reportId)
    {
        return $this->state(function () use ($reportId) {
            return [
                'report_id' => $reportId,
            ];
        });
    }
}

// end
