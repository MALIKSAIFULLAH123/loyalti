<?php

namespace MetaFox\Video\Http\Requests\v1\Video;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Video\Http\Controllers\Api\v1\VideoController::store;
 * stub: api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class FetchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'link' => ['required', 'url'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'link.url' => __p('video::validation.invalid_video_link'),
        ];
    }
}
