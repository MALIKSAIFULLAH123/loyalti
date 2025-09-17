<?php

namespace MetaFox\User\Http\Requests\v1\UserPassword;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\MetaFoxPasswordFormatRule;
use MetaFox\User\Models\PasswordResetToken as Token;

/**
 * Class IndexRequest.
 */
class ResetRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id'                   => ['required', 'numeric', 'exists:user_entities,id'],
            'token'                     => ['required', 'string', sprintf('exists:%s,value', Token::class)],
            'new_password'              => ['required', new MetaFoxPasswordFormatRule()],
            'new_password_confirmation' => [
                'required_with:new_password', 'same:new_password',
            ],
            'logout_others'             => ['sometimes', 'nullable', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Only allow spaces between characters
        if (Arr::has($data, 'new_password')) {
            $data['password'] = trim($data['new_password']);
        }

        return $data;
    }

    /**
     * @return array<string,string>
     */
    public function messages()
    {
        return [
            'new_password_confirmation.same' => __p('validation.confirmed', ['attribute' => 'new password']),
        ];
    }
}
