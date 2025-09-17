<?php

namespace MetaFox\Activity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class FeedFactory.
 * @codeCoverageIgnore
 * @ignore
 */
class FeedFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feed::class;

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
