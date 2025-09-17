<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\ConversionRequest;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\ActivityPoint\Http\Controllers\Api\v1\ConversionRequestController;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link ConversionRequestController::index
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
            'status'    => ['sometimes', 'string', new AllowInRule($statusOptions)],
            'from_date' => ['sometimes', 'string'],
            'to_date'   => ['sometimes', 'string'],
            'limit'     => ['sometimes', 'integer', 'min:1', 'max:500'],
            'id'        => ['sometimes', 'integer', 'exists:apt_conversion_requests'],
        ];
    }
}
