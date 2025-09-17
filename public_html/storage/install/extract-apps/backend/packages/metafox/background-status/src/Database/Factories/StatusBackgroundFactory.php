<?php

namespace MetaFox\BackgroundStatus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\BackgroundStatus\Models\StatusBackground;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method StatusBackground create($attributes = [], ?Model $parent = null)
 */
class StatusBackgroundFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StatusBackground::class;

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
