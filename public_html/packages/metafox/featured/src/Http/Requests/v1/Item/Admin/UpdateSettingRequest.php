<?php

namespace MetaFox\Featured\Http\Requests\v1\Item\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Platform\Rules\AllowInRule;

class UpdateSettingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'role_id'     => ['required', 'integer', new AllowInRule(Feature::getAllowedRole()), 'exists:auth_roles,id'],
            'permissions' => ['required', 'array', 'min:1'],
        ];
    }
}
