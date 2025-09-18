<?php

namespace MetaFox\Like\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class LikeAggFactory.
 * @method LikeAgg create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class LikeAggFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LikeAgg::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reaction_id' => mt_rand(1, 6),
        ];
    }
}

// end
