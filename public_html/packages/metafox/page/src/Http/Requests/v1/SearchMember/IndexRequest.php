<?php

namespace MetaFox\Page\Http\Requests\v1\SearchMember;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Page\Support\Browse\Scopes\SearchMember\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
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
 * @link \MetaFox\Page\Http\Controllers\Api\v1\SearchMemberController::index
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
            'q'       => ['sometimes', 'string', 'nullable'],
            'page_id' => ['required', 'numeric', 'exists:pages,id'],
            'view'    => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'limit'   => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['q'])) {
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }
        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
