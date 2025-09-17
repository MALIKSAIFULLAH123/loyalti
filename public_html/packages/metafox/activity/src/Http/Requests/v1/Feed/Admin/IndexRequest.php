<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\FeedAdminController::indexRequest
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
            'q'          => ['sometimes', 'nullable', 'string'],
            'user_name'  => ['sometimes', 'nullable', 'string'],
            'owner_name' => ['sometimes', 'nullable', 'string'],
            'page'       => ['sometimes', 'numeric', 'min:1'],
            'limit'      => ['sometimes', 'numeric', new PaginationLimitRule(max: 100)],
            'type_id'    => ['sometimes', 'string', 'exists:activity_types,type'],
            'item_type'  => ['sometimes', 'string', 'exists:activity_types,entity_type'],
            'from_date'  => ['sometimes', 'nullable', 'string'],
            'to_date'    => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
