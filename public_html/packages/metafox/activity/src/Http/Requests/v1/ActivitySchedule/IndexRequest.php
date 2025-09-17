<?php

namespace MetaFox\Activity\Http\Requests\v1\ActivitySchedule;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\ActivityScheduleController::index
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
            'entity_id'   => ['sometimes', 'nullable', 'integer', 'min:1'], // Avoid ambiguity with owner fields and user fields
            'entity_type' => ['sometimes', 'nullable', 'string'],
            'page'        => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'       => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['entity_id'])) {
            $data['entity_id'] = null;
        }

        if (!isset($data['entity_type'])) {
            $data['entity_type'] = null;
        }

        return $data;
    }
}
