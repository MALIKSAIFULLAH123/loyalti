<?php

namespace MetaFox\Report\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Report\Models\ReportItemAggregate;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class ReportItemAggregateFactory.
 * @method ReportItemAggregate create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class ReportItemAggregateFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReportItemAggregate::class;

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
