<?php

namespace MetaFox\Profile\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Profile\Models\Section;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class SectionFactory.
 * @method Section create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class SectionFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Section::class;

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
