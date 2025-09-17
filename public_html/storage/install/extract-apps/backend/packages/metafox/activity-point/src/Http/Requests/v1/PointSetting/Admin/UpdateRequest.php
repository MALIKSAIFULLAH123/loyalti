<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PointSetting\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PointSettingAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'points'     => ['required', 'integer', 'gte:0'],
            'max_earned' => ['required', 'integer', 'gte:0', 'lte:2' . str_repeat(0, 9)],
            'period'     => ['required', 'integer', 'gte:0', 'lte:2' . str_repeat(0, 9)],
        ];
    }
}
