<?php

namespace MetaFox\User\Traits;

use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Policies\UserPolicy;

trait UserLocationTrait
{
    /**
     * @return array<string, mixed>
     */
    public function getLocation(UserContract $context, User $resource): array
    {
        $locationData = [
            'country_iso'        => null,
            'city_location'      => null,
            'postal_code'        => null,
            'country_city_code'  => null,
            'country_state_id'   => null,
            'country_name'       => null,
            'country_state_name' => null,
        ];

        if ($this->canViewLocation($context, $resource)) {
            $profile = $resource->profile;

            $locationData = [
                'country_iso'        => $profile?->country_iso,
                'city_location'      => $profile?->city_location,
                'postal_code'        => $profile?->postal_code,
                'country_city_code'  => $profile?->country_city_code,
                'country_state_id'   => $profile?->country_state_id,
                'country_name'       => $profile?->country_iso ? Country::getCountryName($profile->country_iso) : null,
                'country_state_name' => $profile?->country_iso ? Country::getCountryStateName($profile->country_iso, $profile->country_state_id) : null,
            ];
        }

        return $locationData;
    }

    public function canViewLocation(UserContract $context, UserContract $user): bool
    {
        return policy_check(UserPolicy::class, 'viewLocation', $context, $user);
    }

    public function getLocationValue(UserContract $context, $resource): string
    {
        if (!$resource instanceof HasUserProfile) {
            return '';
        }

        $locationData = $this->getLocation($context, $resource);
        $county       = Arr::get($locationData, 'country_name');
        $state        = Arr::get($locationData, 'country_state_name');
        $city         = Arr::get($locationData, 'city_location');

        if (empty($city) && empty($state) && empty($county)) {
            return '';
        }

        return __p('user::phrase.format_location_by_city_state_country', [
            'hasCountry' => (int) !empty($county),
            'hasState'   => (int) !empty($state),
            'hasCity'    => (int) !empty($city),
            'country'    => $county,
            'city'       => $city,
            'state'      => $state,
        ]);
    }

    public function hasLocation(UserContract $context): bool
    {
        $profile = $context->profile;

        if (!$profile instanceof UserProfile) {
            return false;
        }

        if (!empty($profile->country_city_code)) {
            return true;
        }

        if (!empty($profile->country_iso)) {
            return true;
        }

        if (!empty($profile->country_state_id)) {
            return true;
        }

        return false;
    }
}
