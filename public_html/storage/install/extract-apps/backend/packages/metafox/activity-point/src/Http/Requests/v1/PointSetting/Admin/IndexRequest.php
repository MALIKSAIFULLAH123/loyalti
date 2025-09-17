<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PointSetting\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Support\Facade\PointSetting;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PointSettingAdminController::index
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
    public function rules()
    {
        return [
            'sort'      => SortScope::rules(),
            'sort_type' => SortScope::sortTypes(),
            'page'      => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'     => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
            'role_id'   => ['sometimes', 'integer', 'nullable', new AllowInRule(PointSetting::getAllowedRole())],
            'module_id' => ['sometimes', 'string', 'nullable'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'sort')) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!Arr::has($data, 'sort_type')) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
        }

        if (!Arr::has($data, 'module_id')) {
            $data['module_id'] = MetaFoxConstant::EMPTY_STRING;
        }

        if (!Arr::has($data, 'role_id')) {
            $data['role_id'] = UserRole::NORMAL_USER_ID;
        }

        if (!Arr::has($data, 'limit')) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
