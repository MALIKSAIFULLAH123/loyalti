<?php

namespace MetaFox\Activity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use MetaFox\Activity\Models\Snooze;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class SnoozeFactory.
 * @codeCoverageIgnore
 * @ignore
 */
class SnoozeFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Snooze::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'           => 1,
            'user_type'         => 'user',
            'owner_id'          => 1,
            'owner_type'        => 'user',
            'snooze_until'      => Carbon::now()->addDays(30),
            'is_snooze_forever' => 0,
        ];
    }
}
