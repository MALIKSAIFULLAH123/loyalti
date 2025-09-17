<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\UserActivity;

/**
 * Class UserActivityFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 * @method UserActivity create($attributes = [], ?Model $parent = null)
 */
class UserActivityFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = now();

        return [
            'last_activity'   => $now,
            'last_login'      => $now,
            'last_ip_address' => Request::ip(),
        ];
    }
}

// end
