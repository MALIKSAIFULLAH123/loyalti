<?php

namespace MetaFox\BackgroundStatus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\BackgroundStatus\Models\RecentUsed;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method RecentUsed create($attributes = [], ?Model $parent = null)
 */
class RecentUsedFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecentUsed::class;

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
