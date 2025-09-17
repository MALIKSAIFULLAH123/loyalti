<?php

namespace MetaFox\Notification\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Notification\Models\NotificationChannel;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class NotificationChannelFactory.
 *
 * @method NotificationChannel create($attributes = [], ?Model $parent = null)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationChannelFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NotificationChannel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'  => $this->faker->name,
            'title' => $this->faker->title,
        ];
    }
}

// end
