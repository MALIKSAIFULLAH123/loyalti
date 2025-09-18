<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Answers;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class AnswersFactory.
 * @method Answers create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class AnswersFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Answers::class;

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
