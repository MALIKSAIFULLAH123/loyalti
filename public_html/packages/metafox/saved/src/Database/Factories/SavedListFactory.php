<?php

namespace MetaFox\Saved\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Saved\Models\SavedList;

/**
 * Class SavedListFactory.
 * @method SavedList create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class SavedListFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SavedList::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
