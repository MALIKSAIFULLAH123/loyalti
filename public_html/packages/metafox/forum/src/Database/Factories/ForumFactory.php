<?php

namespace MetaFox\Forum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Models\Forum;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method Forum create($attributes = [], ?Model $parent = null)
 */
class ForumFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Forum::class;

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
