<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\UserVerifyError;

/**
 * Class UserVerifyErrorFactory.
 * @method UserVerifyError create($attributes = [], ?Model $parent = null)
 * @codeCoverageIgnore
 * @ignore
 */
class UserVerifyErrorFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserVerifyError::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hash_code'  => 'example hash code',
            'ip_address' => '127.0.0.1',
            'email'      => 'example@example.com',
        ];
    }
}

// end
