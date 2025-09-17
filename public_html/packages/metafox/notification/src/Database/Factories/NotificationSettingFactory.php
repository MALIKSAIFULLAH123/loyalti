<?php

namespace MetaFox\Notification\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Notification\Models\NotificationSetting;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class NotificationSettingFactory.
 *
 * @method NotificationSetting create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationSettingFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NotificationSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_value' => 1,
        ];
    }
}

// end
