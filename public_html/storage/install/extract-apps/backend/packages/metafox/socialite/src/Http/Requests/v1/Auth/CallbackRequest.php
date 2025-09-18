<?php

namespace MetaFox\Socialite\Http\Requests\v1\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CallbackRequest.
 */
class CallbackRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'code'          => ['sometimes', 'string'],
            'token'         => ['sometimes', 'string'],
            'code_verifier' => ['sometimes', 'string'],
            'display_name'  => ['sometimes', 'nullable', 'string'],
        ];
    }
}
