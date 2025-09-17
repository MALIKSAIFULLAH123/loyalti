<?php

namespace MetaFox\Blog\Http\Requests\v1\Blog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::patch;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class PatchRequest
 * @ignore
 */
class PatchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'is_sponsor'      => 'sometimes|integer|in:0,1',
            'is_feature'      => 'sometimes|integer|in:0,1',
            'sponsor_in_feed' => 'sometimes|integer|in:0,1',
        ];
    }
}
