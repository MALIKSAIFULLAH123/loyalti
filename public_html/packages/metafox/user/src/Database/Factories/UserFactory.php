<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;

/**
 * Class UserFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 * @method User create($attributes = [], ?Model $parent = null)
 */
class UserFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $full_name = $this->faker->name;
        $firstName = $this->faker->firstName;
        $lastName  = $this->faker->lastName;
        $userName  = uniqid('test');

        $email = $userName . '@phpfox.com';

        return [
            'full_name'         => $full_name,
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'user_name'         => $userName,
            'email'             => $email,
            'email_verified_at' => Carbon::now(),
            'verified_at'       => Carbon::now(),
            'approve_status'    => MetaFoxConstant::STATUS_APPROVED,
            'password'          => Hash::make('123456'), // password
            'remember_token'    => null,
            'is_featured'       => rand(0, 1),
            'profile'           => [
                'full_name'      => $full_name,
                'avatar_file_id' => $this->sampleFile('photo', 'avatar', 80)?->id,
                'cover_file_id'  => $this->sampleFile('photo', 'cover', 80)?->id,
            ],
        ];
    }

    /**
     * @param string      $username
     * @param string      $password
     * @param string|null $email
     * @param string|null $fullName
     *
     * @return self
     */
    public function asSuperAdmin(
        string $username,
        string $password,
        string $email = null,
        string $fullName = null
    ): self {
        if ($email === null) {
            $validator = Validator::make(['email' => $username], [
                'email' => 'required|email',
            ]);

            $email = $username;
            if (!$validator->passes()) {
                $email = "{$username}@phpfox.com";
            }
        }

        // test admin exists.
        return $this->state(function () use ($username, $password, $email, $fullName) {
            return [
                'user_name' => $username,
                'full_name' => $fullName === null ? $username : $fullName,
                'email'     => $email,
                'password'  => Hash::make($password),
                'profile'   => [
                    'language_id' => App::getLocale(),
                ],
            ];
        });
    }

    public function seed()
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole(UserRole::NORMAL_USER_ID);
        });
    }
}
