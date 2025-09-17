<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\ConversionRequest\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\ActivityPoint\Http\Controllers\Api\ConversionRequestAdminController;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link ConversionRequestAdminController::deny
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest
 */
class DenyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'reason' => ['required', 'string']
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data['reason'] = parse_input()->clean($data['reason']);

        return $data;
    }
}
