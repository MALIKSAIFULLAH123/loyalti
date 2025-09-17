<?php

namespace MetaFox\Saved\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Saved\Models\SavedListData;

/**
 * Class SavedListDataFactory.
 *
 * @method SavedListData create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class SavedListDataFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SavedListData::class;

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
