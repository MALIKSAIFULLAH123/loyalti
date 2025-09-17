<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

use ArrayObject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\User\Support\Browse\Scopes\User\RoleScope;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\User\Support\Browse\Scopes\User\StatusScope;
use MetaFox\User\Support\Browse\Scopes\User\ViewScope;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserAdminController::index;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = new ArrayObject([
            'q'                => ['sometimes', 'nullable', 'string'],
            'email'            => ['sometimes', 'nullable', 'string'],
            'phone_number'     => ['sometimes', 'nullable', 'string'],
            'group'            => ['sometimes', 'nullable', 'numeric', new AllowInRule(RoleScope::getAllowRoles())],
            'gender'           => ['sometimes', 'nullable', 'numeric', new ExistIfGreaterThanZero('exists:user_gender,id')],
            'postal_code'      => ['sometimes', 'nullable', 'string'],
            'view'             => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'page'             => ['sometimes', 'numeric', 'min:1'],
            'age_from'         => ['sometimes', 'nullable', 'numeric', 'min:4', 'max:121'],
            'age_to'           => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:121'],
            'limit'            => ['sometimes', 'numeric', 'min:10'],
            'sort'             => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'        => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'country'          => ['sometimes', 'string', 'nullable', 'exists:core_countries,country_iso'],
            'country_state_id' => ['sometimes', 'nullable', 'string'],
            'city_code'        => ['sometimes', 'nullable'],
            'status'           => ['sometimes', 'nullable', 'string', new AllowInRule(StatusScope::getAllowStatus())],
            'ip_address'       => ['sometimes', 'nullable', 'string'],
            'currency_id'      => ['sometimes', 'nullable', 'string'],
        ]);

        CustomFieldFacade::loadFieldSearchRules($rules, CustomField::SECTION_TYPE_USER);

        return $rules->getArrayCopy();
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['view'])) {
            $data['view'] = ViewScope::VIEW_DEFAULT;
        }

        if (!isset($data['q'])) {
            $data['q'] = '';
        }

        if (!isset($data['sort'])) {
            $data['sort'] = SortScope::SORT_CREATED_AT;
        }

        if (!isset($data['sort_type'])) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (Arr::has($data, 'age_from')) {
            $minDate = Carbon::today()->subYears(Arr::get($data, 'age_from'));
            Arr::set($data, 'age_from', $minDate);
        }

        if (Arr::has($data, 'age_to')) {
            $maxDate = Carbon::today()->subYears(Arr::get($data, 'age_to'))->endOfDay();
            Arr::set($data, 'age_to', $maxDate);
        }

        $data = CustomFieldFacade::handleValidatedCustomFieldsForSearch($data, [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        return $data;
    }
}
