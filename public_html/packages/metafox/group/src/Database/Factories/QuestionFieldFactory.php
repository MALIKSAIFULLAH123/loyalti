<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\QuestionField;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class QuestionFieldFactory.
 *
 * @method QuestionField create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class QuestionFieldFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = QuestionField::class;

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
