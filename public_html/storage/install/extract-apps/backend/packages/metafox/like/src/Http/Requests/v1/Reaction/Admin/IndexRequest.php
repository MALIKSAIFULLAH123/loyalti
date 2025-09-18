<?php

namespace MetaFox\Like\Http\Requests\v1\Reaction\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Like\Http\Controllers\Api\v1\ReactionAdminController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest
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
            'q'         => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'nullable', new AllowInRule([0, 1])],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'limit'     => ['sometimes', 'numeric', 'min:10'],
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
