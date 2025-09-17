<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\User\Rules\ActiveReasonRule;
use MetaFox\User\Rules\ValidatePasswordRule;

/**
 * Class DeleteRequest.
 */
class DeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $context = user();
        $rules   = [
            'reason_id' => ['sometimes', 'nullable', 'numeric', new ActiveReasonRule()],
            'feedback'  => ['sometimes', 'string', 'nullable'],
        ];

        if ($context->getAuthPassword()) {
            Arr::set($rules, 'password', ['required', 'string', new ValidatePasswordRule($context)]);
        }

        return $rules;
    }
}
