<?php

namespace MetaFox\Report\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Report\Models\Report;

class ReportFactory extends Factory
{
    use HasSetState;
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'reason_id' => mt_rand(1, 2),
            'user_type' => 'user',
            'user_id'   => 1,
            'item_type' => 'blog',
            'item_id'   => 1,
            'feedback'  => $this->faker->paragraph(2),
        ];
    }
}
