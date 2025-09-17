<?php

namespace MetaFox\ChatPlus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\ChatPlus\Models\Job;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class JobFactory.
 * @method Job create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class JobFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Job::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_sent' => 0,
        ];
    }
}

// end
