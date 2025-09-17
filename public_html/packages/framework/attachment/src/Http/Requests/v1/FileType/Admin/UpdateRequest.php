<?php

namespace MetaFox\Attachment\Http\Requests\v1\FileType\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Attachment\Http\Controllers\Api\v1\FileTypeAdminController::update
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
    public function rules()
    {
        return [
            'extension' => ['sometimes', 'string'],
            'mime_type' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
