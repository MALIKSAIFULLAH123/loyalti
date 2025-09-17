<?php

namespace MetaFox\Authorization\Database\Factories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Authorization\Models\UserDevice;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class DeviceFactory.
 * @method UserDevice create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class UserDeviceFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserDevice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_token'     => Hash::make($this->faker->word),
            'device_id'        => 'iPhone11,8',
            'device_uid'       => $this->faker->uuid,
            'token_source'     => 'firebase',
            'platform'         => UserDevice::DEVICE_IOS_PLATFORM,
            'platform_version' => 30,
            'is_active'        => 1,
            'created_at'       => Carbon::now(),
            'updated_at'       => Carbon::now(),
        ];
    }
}
