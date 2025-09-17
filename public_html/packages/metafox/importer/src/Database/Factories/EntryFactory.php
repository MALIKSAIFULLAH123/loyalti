<?php

namespace MetaFox\Importer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Importer\Models\Entry;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class EntryFactory.
 * @method Entry create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class EntryFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Entry::class;

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
