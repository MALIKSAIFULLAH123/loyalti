<?php

namespace MetaFox\Ban\Http\Requests\v1\BanRule\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class BatchDeleteRequest.
 */
class BatchDeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id'   => ['required', 'array'],
            'id.*' => ['sometimes', 'numeric', 'exists:user_ban_rules,id'],
        ];
    }
}
