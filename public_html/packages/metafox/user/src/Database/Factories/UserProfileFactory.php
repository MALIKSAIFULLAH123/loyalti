<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Core\Support\Facades\CountryCity;
use MetaFox\User\Models\UserProfile;

/**
 * Class UserProfileFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 */
class UserProfileFactory extends Factory
{
    public $model = UserProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $defaultCity = CountryCity::getCity(CountryCity::getDefaultCityCode());

        return [
            'full_phone_number' => $this->faker->phoneNumber,
            'gender_id'         => rand(1, 2),
            'city_location'     => $defaultCity?->name ?? '',
            'country_iso'       => Country::getDefaultCountryIso(),
            'country_state_id'  => Country::getDefaultCountryStateIso(),
            'country_city_code' => $defaultCity?->city_code ?? 0,
            'postal_code'       => 1,
            'birthday'          => $this->faker->date(),
            'relation'          => rand(1, 10),
        ];
    }
}
