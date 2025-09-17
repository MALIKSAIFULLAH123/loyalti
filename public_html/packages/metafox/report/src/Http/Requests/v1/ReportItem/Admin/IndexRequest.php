<?php

namespace MetaFox\Report\Http\Requests\v1\ReportItem\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Report\Http\Controllers\Api\v1\ReportItemAdminController::index;
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
            'aggregate_id' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:report_item_aggregate,id')],
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', 'min:10'],
        ];
    }

    /**
     * @param  array<string< mixed>|int|string|null $key
     * @param  mixed                $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $defaults = [
            'page'  => 1,
            'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];

        return array_merge($defaults, parent::validated($key, $default));
    }
}
