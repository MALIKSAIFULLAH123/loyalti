<?php

namespace MetaFox\Socialite\Http\Requests\v1\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class LoginRequest.
 */
class LoginRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'provider' => $this->route('provider'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'provider' => ['required', 'string'],
        ];
    }
}
