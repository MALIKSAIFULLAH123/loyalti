<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use MetaFox\Authorization\Models\Role;
use MetaFox\Ban\Rules\BanEmailRule;
use MetaFox\Core\Support\Facades\CountryCity as CityFacade;
use MetaFox\Localize\Models\CountryCity;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\RegexUsernameRule;
use MetaFox\Platform\Rules\UniqueEmail;
use MetaFox\Platform\Rules\UniqueSlug;
use MetaFox\Sms\Rules\PhoneNumberRule;
use MetaFox\User\Http\Requests\v1\User\UpdateRequest as UserUpdateRequest;
use MetaFox\User\Models\User;
use MetaFox\User\Rules\AssignRoleRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserAdminController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends UserUpdateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $context = user();
        $userId  = $this->route('user');
        $user    = User::find($userId);

        $rules = new \ArrayObject([
            'role_id'      => ['sometimes', 'integer', Rule::exists(Role::class, 'id'), new AssignRoleRule($context)],
            'user_name'    => ['required', 'string', new UniqueSlug('user', $userId), new RegexUsernameRule()],
            'full_name'    => ['required', 'string'],
            'email'        => ['required', 'email', new UniqueEmail($userId), new BanEmailRule($user->email)],
            'password'     => ['sometimes', 'nullable', 'string', $this->getPasswordRule()],
            'birthday'     => ['sometimes', 'nullable', 'date'],
            'phone_number' => [
                'sometimes',
                'string',
                'nullable',
                new PhoneNumberRule(),
                Rule::unique('users', 'phone_number')->ignore($userId),
            ],
            'postal_code'       => ['sometimes', 'nullable', 'string'],
            'country_iso'       => ['sometimes', 'nullable', 'exists:core_countries,country_iso'],
            'country_state_id'  => ['sometimes', 'nullable', 'string'],
            'country_city_code' => ['sometimes', 'nullable'],
            'gender'            => [
                'sometimes', 'nullable', 'numeric', 'nullable', new ExistIfGreaterThanZero('exists:user_gender,id'),
            ],
            'language_id' => ['sometimes', 'string', 'nullable', 'exists:core_languages,language_code,is_active,1'],
            'avatar'      => ['sometimes', 'nullable', 'array'],
            'address'     => ['sometimes', 'nullable', 'string'],
        ]);

        return $rules->getArrayCopy();
    }

    /**
     * @param  string               $key
     * @param  mixed                $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data   = parent::validated($key, $default);
        $userId = $this->route('user');
        $user   = User::find($userId);

        if (Arr::has($data, 'password') && !Arr::get($data, 'password')) {
            Arr::forget($data, 'password');
        }

        $this->handleGender($data);
        $this->transformCountryState($data);
        $this->transformCityCode($data);
        $this->handleProfileFields($data);

        if (Arr::has($data, 'profile.phone_number')) {
            Arr::set($data, 'phone_number', Arr::get($data, 'profile.phone_number'));
        }

        app('events')->dispatch('user.validate_mfa_field_for_request', [$user, $data], true);

        return $data;
    }

    protected function handleGender(array &$data)
    {
        $gender = Arr::get($data, 'gender') ?? 0;

        $customGender = Arr::get($data, 'custom_gender') ?? 0;

        Arr::set($data, 'gender_id', max($gender, $customGender));

        if ($gender > 0) {
            Arr::set($data, 'gender_id', $gender);
        }
    }

    protected function transformCountryState(array &$data): void
    {
        $countryStateId = Arr::get($data, 'country_state_id') ?? 0;
        if ($countryStateId) {
            return;
        }

        $countryState = Arr::get($data, 'country_state') ?? 0;
        if (is_array($countryState)) {
            $countryStateId = Arr::get($countryState, 'value') ?? 0;
        }

        Arr::set($data, 'country_state_id', $countryStateId);
    }

    protected function handleProfileFields(array &$data): void
    {
        $attributes = [
            'country_iso', 'country_state_id', 'country_city_code', 'language_id',
            'city_location', 'postal_code', 'gender_id', 'birthday', 'address',
        ];

        foreach ($attributes as $attribute) {
            Arr::set($data, 'profile.' . $attribute, Arr::get($data, $attribute));
        }

        Arr::forget($data, [...$attributes, 'gender', 'custom_gender']);
    }

    /**
     * @param  array<string, mixed> $data
     * @return void
     */
    protected function transformCityCode(array &$data): void
    {
        $cityCode = Arr::get($data, 'country_city_code') ?? 0;
        if (is_array($cityCode)) {
            $cityCode = Arr::get($cityCode, 'value') ?? 0;
        }

        $data['country_city_code'] = $cityCode;
        $city                      = CityFacade::getCity($cityCode);
        $data['city_location']     = $city instanceof CountryCity ? $city->name : '';
    }
}
