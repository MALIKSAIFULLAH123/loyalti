<?php

namespace MetaFox\User\Http\Requests\v1\User;

use ArrayObject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\CountryCity as CityFacade;
use MetaFox\Localize\Models\CountryCity;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\User\Support\Browse\Scopes\User\RoleScope;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\User\Support\Browse\Scopes\User\ViewScope;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserController::index;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = new ArrayObject([
            'q'                => ['sometimes', 'nullable', 'string'],
            'view'             => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'gender'           => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_gender,id')],
            'page'             => ['sometimes', 'numeric', 'min:1'],
            'limit'            => ['sometimes', 'numeric', new PaginationLimitRule()],
            'sort'             => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'        => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'country'          => ['sometimes', 'string', 'min:2'],
            'country_state_id' => ['sometimes', 'nullable', 'string'],
            'city'             => ['sometimes', 'nullable', 'string'],
            'city_code'        => ['sometimes', 'nullable', 'string'],
            'is_featured'      => ['sometimes', 'numeric'],
            'group'            => ['sometimes', 'nullable', 'numeric', new AllowInRule(RoleScope::getAllowFilterRoles())],
        ]);

        CustomFieldFacade::loadFieldSearchRules($rules, CustomField::SECTION_TYPE_USER);

        return $rules->getArrayCopy();
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $data = array_merge($data, $this->transformLocation($data));

        $data    = Arr::add($data, 'view', ViewScope::VIEW_DEFAULT);
        $data    = Arr::add($data, 'q', '');
        $data    = Arr::add($data, 'sort', SortScope::getSortDefault());
        $data    = Arr::add($data, 'sort_type', SortScope::getDefaultSortType(Arr::get($data, 'sort')));
        $data    = Arr::add($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $country = Arr::get($data, 'country');

        if (null === $country) {
            Arr::forget($data, ['country', 'country_state_id']);
        }

        $isFeatured = Arr::get($data, 'is_featured');
        if (!$isFeatured) {
            $data['is_featured'] = null;
        }

        return CustomFieldFacade::handleValidatedCustomFieldsForSearch($data, [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_SEARCH,
        ]);
    }

    protected function transformLocation(array $data): array
    {
        $countryStateId = Arr::get($data, 'country_state_id', 0);
        $countryIso     = Arr::get($data, 'country');
        $cityCode       = Arr::get($data, 'city_code', 0);
        $cityLocation   = MetaFoxConstant::EMPTY_STRING;

        if (is_array($cityCode)) {
            $cityCode = Arr::get($cityCode, 'value', 0);
        }

        if ($cityCode != null) {
            $city         = CityFacade::getCity($cityCode);
            $cityLocation = $city instanceof CountryCity ? $city->name : MetaFoxConstant::EMPTY_STRING;
        }

        if (is_array($countryStateId)) {
            $countryStateId = Arr::get($countryStateId, 'value', 0);
        }

        return [
            'city_code'        => $cityCode ?? 0,
            'city'             => $cityLocation,
            'country_state_id' => $countryStateId ?? 0,
            'country'          => $countryIso,
        ];
    }
}
