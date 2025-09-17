<?php

namespace MetaFox\Story\Http\Requests\v1\Story\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Story\Http\Controllers\Api\v1\StoryServiceAdminController::index
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
    public function rules(): array
    {
        return [
            'q'     => ['sometimes', 'string', 'nullable'],
            'limit' => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $data = Arr::add($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        return $data;
    }
}
