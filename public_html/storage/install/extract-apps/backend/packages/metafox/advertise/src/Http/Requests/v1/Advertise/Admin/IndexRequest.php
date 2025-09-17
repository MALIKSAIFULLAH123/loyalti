<?php

namespace MetaFox\Advertise\Http\Requests\v1\Advertise\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Advertise\Http\Controllers\Api\v1\AdvertiseAdminController::index
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
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', 'min:1'],
            'placement_id' => ['sometimes', 'numeric'],
            'start_date'   => ['sometimes', 'nullable', 'string'],
            'end_date'     => ['sometimes', 'nullable', 'string'],
            'title'        => ['sometimes', 'string', 'max:' . MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH],
            'full_name'    => ['sometimes', 'string', 'max:' . MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH],
            'status'       => ['sometimes', 'string'],
            'is_active'    => ['sometimes', 'nullable', new AllowInRule([0, 1])],
        ];
    }
}
