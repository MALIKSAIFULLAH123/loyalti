<?php

namespace MetaFox\Follow\Http\Requests\v1\Follow;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Follow\Support\Browse\Scopes\ViewScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Follow\Http\Controllers\Api\v1\FollowController::index
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
            'q'       => ['sometimes', 'nullable', 'string'],
            'view'    => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'user_id' => ['required_if:view,following', 'numeric', 'exists:user_entities,id'],
            'page'    => ['sometimes', 'numeric', 'min:1'],
            'limit'   => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE_SPECIAL_CASE;
        }

        if (!isset($data['view'])) {
            $data['view'] = '';
        }

        if (!isset($data['q'])) {
            $data['q'] = '';
        }

        if (!Arr::has($data, 'user_id')) {
            Arr::set($data, 'user_id', 0);
        }

        return $data;
    }
}
