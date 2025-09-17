<?php

namespace MetaFox\Report\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Report\Models\ReportOwner;

/**
 * @method ReportOwner create($attributes = [], ?Model $parent = null)
 */
class ReportOwnerFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReportOwner::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total_report' => 0,
        ];
    }
}

// end
