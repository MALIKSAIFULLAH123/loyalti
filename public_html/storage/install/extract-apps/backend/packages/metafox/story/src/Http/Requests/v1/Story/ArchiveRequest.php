<?php

namespace MetaFox\Story\Http\Requests\v1\Story;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Story\Http\Controllers\Api\v1\StoryViewController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ArchiveRequest.
 */
class ArchiveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'story_id' => ['required', 'numeric', 'exists:stories,id'],
        ];
    }
}
