<?php

namespace MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\LiveStreaming\Http\Controllers\Api\v1\LiveVideoController::validateStreamKey
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StreamKeyValidateRequest.
 */
class StreamKeyValidateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'stream_key' => ['required', 'string'],
        ];
    }
}
