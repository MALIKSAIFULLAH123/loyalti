<?php

namespace MetaFox\Layout\Http\Requests\v1\Snippet;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Layout\Http\Controllers\Api\v1\SnippetController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateVariantRequest.
 */
class UpdateVariantRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'theme'   => ['required', 'string'],
            'variant' => ['required', 'string'],
            'files'   => ['required', 'array'],
            'active'  => ['sometimes', 'boolean', 'nullable'],
        ];
    }
}
