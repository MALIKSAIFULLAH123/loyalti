<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\ConversionRequest\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\ActivityPoint\Http\Controllers\Api\ConversionRequestAdminController;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link ConversionRequestAdminController::index
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
        $statusOptions = array_column(PointConversion::getConversionRequestStatusOptions(), 'value');

        return [
            'status' => ['sometimes', 'string', new AllowInRule($statusOptions)],
            'from_date' => ['sometimes', 'string'],
            'to_date'   => ['sometimes', 'string'],
            'creator'   => ['sometimes', 'string'],
            'limit'     => ['sometimes', 'integer', 'min:1', 'max:' . Pagination::DEFAULT_MAX_ITEM_PER_PAGE],
            'id'        => ['sometimes', 'integer', 'exists:apt_conversion_requests'],
        ];
    }
}
