<?php

namespace MetaFox\Activity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Activity\Models\Hidden;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class HiddenFactory.
 * @codeCoverageIgnore
 * @ignore
 */
class HiddenFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Hidden::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'   => 1,
            'user_type' => 'user',
            'feed_id'   => 1,
        ];
    }
}

// end
