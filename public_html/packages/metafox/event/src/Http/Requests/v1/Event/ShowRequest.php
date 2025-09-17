<?php

namespace MetaFox\Event\Http\Requests\v1\Event;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Event\Http\Controllers\Api\v1\EventController::store;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ShowRequest.
 */
class ShowRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'invite_code' => ['sometimes', 'string'],
        ];
    }
}
