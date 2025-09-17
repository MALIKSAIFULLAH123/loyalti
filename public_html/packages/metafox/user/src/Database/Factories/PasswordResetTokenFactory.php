<?php

namespace MetaFox\User\Database\Factories;

use Exception;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\User\Models\PasswordResetToken;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class PasswordResetTokenFactory.
 * @method PasswordResetToken create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class PasswordResetTokenFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PasswordResetToken::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'value'      => random_int(100000, 999999),
            'expired_at' => Carbon::now()->addMinutes(3),
        ];
    }
}

// end
