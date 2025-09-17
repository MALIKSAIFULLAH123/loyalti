<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\UserPrivacyResource;

/**
 * Class UserPrivacyResourceFactory.
 * @packge MetaFox\User\Database\Factories
 * @ignore
 * @codeCoverageIgnore
 */
class UserPrivacyResourceFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPrivacyResource::class;

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
