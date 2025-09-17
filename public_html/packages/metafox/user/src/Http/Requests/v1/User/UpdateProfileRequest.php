<?php

namespace MetaFox\User\Http\Requests\v1\User;

use ArrayObject;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\CountryCity as CityFacade;
use MetaFox\Form\RelationTrait;
use MetaFox\Localize\Models\CountryCity;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class UpdateProfileRequest.
 */
class UpdateProfileRequest extends FormRequest
{
    use RelationTrait;

    /**
     * Get the validation rules that apply to the request.$user.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $rules  = new ArrayObject([]);
        $userId = $this->route('id');

        $user = user();
        if ($userId > 0) {
            $user = UserEntity::getById($userId)->detail;
        }

        CustomFieldFacade::loadFieldEditRules($user, $rules, ['section_type' => CustomField::SECTION_TYPE_USER]);

        return $rules->getArrayCopy();
    }

    public function validated($key = null, $default = null)
    {
        $data   = parent::validated($key, $default);
        $params = $this->getQueryParamsCustomFields();

        $userId = $this->route('id');

        $user = $context = user();
        if ($userId > 0) {
            $user = UserEntity::getById($userId)->detail;
        }

        $data = CustomFieldFacade::handleCustomProfileFieldsForEdit($user, $data, $params);
        $data = CustomFieldFacade::filterVisibleRoleFieldsForEdit($context, $user, $data, $params);

        return $this->handleProfile($data);
    }

    /**
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function messages(): array
    {
        $message = [
            'country_iso.required'      => __p('user::phrase.country_is_a_required_field'),
            'birthday.required'         => __p('user::phrase.birthday_is_a_required_field'),
            'gender.required'           => __p('user::phrase.gender_is_a_required_field'),
            'relation.numeric'          => __p('user::phrase.relationship_status_is_a_required_field'),
            'custom_gender.required_if' => __p('user::validation.custom_gender_field_is_a_required_field'),
        ];

        $params = $this->getQueryParamsCustomFields();
        $result = CustomFieldFacade::handleFieldValidationErrorMessage(user(), $params);

        return array_merge($message, $result);
    }

    protected function handleProfile($data): array
    {
        $data = array_merge(
            $data,
            $this->handleRelationField($data),
            $this->handleGenderField($data),
            $this->transformLocation($data),
        );

        $attributes = [
            'country_iso', 'country_state_id', 'country_city_code', 'city_location',
            'postal_code', 'gender_id', 'birthday', 'address', 'relation_with', 'relation',
        ];

        foreach ($attributes as $attribute) {
            $value = Arr::get($data, $attribute);
            Arr::set($data, 'profile.' . $attribute, $value);
        }

        Arr::forget($data, [...$attributes, 'gender', 'custom_gender']);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array
     */
    protected function transformLocation(array $data): array
    {
        $countryStateId = Arr::get($data, 'country_state_id', 0);
        $countryIso     = Arr::get($data, 'country_iso');
        $cityCode       = Arr::get($data, 'country_city_code', 0);
        $countryState   = Arr::get($data, 'country_state', 0);
        $cityLocation   = MetaFoxConstant::EMPTY_STRING;

        if (is_array($cityCode)) {
            $cityCode = Arr::get($cityCode, 'value', 0);
        }

        if ($cityCode != null) {
            $city         = CityFacade::getCity($cityCode);
            $cityLocation = $city instanceof CountryCity ? $city->name : MetaFoxConstant::EMPTY_STRING;
        }

        if (is_array($countryState)) {
            $countryStateId = Arr::get($countryState, 'value', 0);
        }

        return [
            'country_city_code' => $cityCode ?? 0,
            'city_location'     => $cityLocation,
            'country_state_id'  => $countryStateId ?? 0,
            'country_iso'       => $countryIso,
            'address'           => Arr::get($data, 'address'),
            'postal_code'       => Arr::get($data, 'postal_code'),
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array
     */
    public function handleGenderField(array $data): array
    {
        $gender = Arr::get($data, 'gender', 0);

        $customGender = Arr::get($data, 'custom_gender', 0);
        $gender       = $gender > 0 ? $gender : max($gender, $customGender);

        return [
            'gender_id' => $gender ?? 0,
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array
     */
    public function handleRelationField(array $data): array
    {
        $relation     = Arr::get($data, 'relation', 0);
        $relationWith = Arr::get($data, 'relation_with', 0);

        if (is_array($relationWith)) {
            $relationWith = Arr::get($relationWith, 'id', 0);
        }

        $withRelations = $this->getWithRelations();
        if (!in_array($relation, $withRelations)) {
            $relationWith = 0;
        }

        return [
            'relation'      => $relation ?? 0,
            'relation_with' => $relationWith ?? 0,
        ];
    }

    private function getQueryParamsCustomFields(): array
    {
        return [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_ALL,
        ];
    }
}
