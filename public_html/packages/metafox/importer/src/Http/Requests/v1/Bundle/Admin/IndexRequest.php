<?php

namespace MetaFox\Importer\Http\Requests\v1\Bundle\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Importer\Support\Browse\Scopes\Bundle\StatusScope;
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
 * @link \MetaFox\Importer\Http\Controllers\Api\v1\BundleAdminController::index
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
            'q'      => ['sometimes', 'nullable', 'string'],
            'limit'  => ['sometimes', 'numeric', new PaginationLimitRule()],
            'status' => ['sometimes', 'nullable', 'string', new AllowInRule(StatusScope::getAllowStatus())],
            'page'   => ['sometimes', 'numeric', 'min:1'],
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
