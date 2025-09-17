<?php

namespace MetaFox\BackgroundStatus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method BgsCollection create($attributes = [], ?Model $parent = null)
 */
class BgsCollectionFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BgsCollection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'              => $this->faker->sentence,
            'main_background_id' => 0,
            'is_active'          => 1,
            'is_default'         => 0,
            'is_deleted'         => 0,
            'total_background'   => 0,
        ];
    }
}
