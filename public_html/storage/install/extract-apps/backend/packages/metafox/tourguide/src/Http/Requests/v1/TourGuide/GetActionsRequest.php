<?php

namespace MetaFox\TourGuide\Http\Requests\v1\TourGuide;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\TourGuide\Http\Controllers\Api\v1\TourGuideController::getActions()
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class GetActionsRequest.
 */
class GetActionsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'page_name' => ['required', 'string'],
        ];
    }
}
