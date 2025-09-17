<?php

namespace MetaFox\User\Http\Requests\v1\UserPassword;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\User\Models\PasswordResetToken as Token;
use MetaFox\User\Support\Facades\User;

/**
 * Class LogoutOtherRequest.
 */
class LogoutOtherRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id'       => ['required', 'numeric', 'exists:user_entities,id'],
            'token'         => ['required', 'string', sprintf('exists:%s,value', Token::class)],
            'logout_others' => ['sometimes', 'nullable', new AllowInRule($this->getLogoutOptions())],
        ];
    }

    /**
     * @return array<string,string>
     */
    public function messages()
    {
        return [];
    }

    protected function getLogoutOptions(): array
    {
        return array_column(User::getLogoutOptions(), 'value');
    }
}
