<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;

class ProfileFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['sometimes', 'numeric', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => __p('user::validation.id.exists'),
        ];
    }
}
