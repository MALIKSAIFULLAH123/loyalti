<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\FeedController::show;
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
            'user_id' => ['sometimes', 'nullable', 'numeric', 'exists:user_entities,id'],
        ];
    }
}
