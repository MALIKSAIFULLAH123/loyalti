<?php

namespace MetaFox\Invite\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Invite\Http\Controllers\Api\v1\InviteController::index
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
            'status'     => ['sometimes', 'nullable', 'string'],
            'user_id'    => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'view'       => ['sometimes', 'nullable', 'string'],
            'page'       => ['sometimes', 'numeric', 'min:1'],
            'start_date' => ['sometimes', 'string'],
            'end_date'   => ['sometimes', 'string', 'after:start_date'],
            'limit'      => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();
        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['user_id'])) {
            $data['user_id'] = 0;
        }

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);

        Arr::set($data, 'q', $q);

        return $data;
    }
}
