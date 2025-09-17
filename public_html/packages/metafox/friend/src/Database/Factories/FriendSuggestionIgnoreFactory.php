<?php

namespace MetaFox\Friend\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Models\FriendSuggestionIgnore;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class FriendSuggestionIgnoreFactory.
 *
 * @method FriendSuggestionIgnore create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendSuggestionIgnoreFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FriendSuggestionIgnore::class;

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
