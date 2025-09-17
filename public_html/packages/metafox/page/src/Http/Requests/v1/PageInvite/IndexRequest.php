<?php

namespace MetaFox\Page\Http\Requests\v1\PageInvite;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageInviteController::index;
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
        return [
            'q'       => ['sometimes', 'nullable', 'string'],
            'page_id' => ['required', 'numeric', 'exists:pages,id'],
            'page'    => ['sometimes', 'numeric', 'min:1'],
            'limit'   => ['sometimes', 'numeric', 'min:10'],
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
