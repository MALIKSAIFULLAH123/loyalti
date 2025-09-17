<?php

namespace MetaFox\Localize\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use MenaraSolutions\Geographer\City;
use MenaraSolutions\Geographer\Earth;
use MenaraSolutions\Geographer\State;
use MetaFox\Localize\Models\Country;
use MetaFox\Localize\Models\CountryChild;
use MetaFox\Localize\Models\CountryCity;
use MetaFox\Platform\PackageManager;

/**
 * Class CountryTablesSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class CountryTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (Country::query()->exists()) {
            return;
        }

        $this->seedCountry();
    }

    private function seedCountry(): bool
    {
        $checkExist = Country::query()->first();
        if (null != $checkExist) {
            return false;
        }

        $countries       = (new Earth())->getCountries();
        $insertCountries = [];
        $insertStates    = [];
        $insertCities    = [];

        $normalizedCountries = collect(config('countries'))->keyBy('country_iso')->toArray();
        $normalizedStates    = collect(config('states'))->keyBy('state_iso')->toArray();

        /** @var \MenaraSolutions\Geographer\Country $country */
        foreach ($countries as $country) {
            $countryData       = $country->toArray();
            $normalizedCountry = Arr::get($normalizedCountries, $countryData['isoCode'], []);

            $insertCountries[] = !empty($normalizedCountry) ? $normalizedCountry : [
                'code'            => $countryData['code'],
                'code3'           => $countryData['code3'],
                'country_iso'     => $countryData['isoCode'],
                'numeric_code'    => $countryData['numericCode'],
                'geonames_code'   => $countryData['geonamesCode'],
                'fips_code'       => $countryData['fipsCode'],
                'area'            => $countryData['area'],
                'currency'        => $countryData['currency'],
                'phone_prefix'    => $countryData['phonePrefix'],
                'mobile_format'   => $countryData['mobileFormat'],
                'landline_format' => $countryData['landlineFormat'],
                'trunk_prefix'    => $countryData['trunkPrefix'],
                'population'      => $countryData['population'],
                'continent'       => $countryData['continent'],
                'language'        => $countryData['language'],
                'name'            => $countryData['name'],
            ];

            $states        = $country->getStates();
            $statesMapping = PackageManager::readFile('metafox/localize', 'resources/states/VN.php');

            /** @var State $state */
            foreach ($states as $state) {
                $stateData       = $state->toArray();
                $normalizedState = Arr::get($normalizedStates, $stateData['isoCode'], []);

                $insertStates[] = !empty($normalizedState) ? $normalizedState : [
                    'country_iso'   => $state->getParentCode(),
                    'state_iso'     => $stateData['isoCode'],
                    'state_code'    => $stateData['code'],
                    'fips_code'     => $stateData['fipsCode'],
                    'geonames_code' => $stateData['geonamesCode'],
                    'post_codes'    => json_encode($stateData['postCodes']),
                    'name'          => Arr::get($statesMapping, $stateData['isoCode'], $stateData['name']),
                    'timezone'      => $stateData['timezone'],
                ];

                $cities = $state->getCities();

                /** @var City $city */
                foreach ($cities as $city) {
                    $cityData = $city->toArray();

                    $insertCities[] = [
                        'state_code'    => $city->getParentCode(),
                        'city_code'     => $cityData['code'],
                        'geonames_code' => $cityData['geonamesCode'],
                        'name'          => $cityData['name'],
                        'latitude'      => $cityData['latitude'],
                        'longitude'     => $cityData['longitude'],
                        'population'    => $cityData['population'],
                        'capital'       => $cityData['capital'],
                    ];
                }
            }

            // release memory ASAP for a large data set
            if (count($insertStates) > 1000) {
                $this->seedStates($insertStates);
            }

            if (count($insertCities) > 1000) {
                $this->seedCities($insertCities);
            }
        }

        Country::query()->insertOrIgnore($insertCountries);
        $this->seedStates($insertStates);
        $this->seedCities($insertCities);

        return true;
    }

    /**
     * @param array<mixed> $insertCities
     */
    private function seedCities(array &$insertCities)
    {
        do {
            CountryCity::query()
                ->insertOrIgnore(array_splice($insertCities, 0, 200));
        } while (!empty($insertCities));
    }

    /**
     * @param array<mixed> $insertStates
     */
    private function seedStates(array &$insertStates)
    {
        do {
            CountryChild::query()
                ->insertOrIgnore(array_splice($insertStates, 0, 200));
        } while (!empty($insertStates));
    }
}
