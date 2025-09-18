<?php

namespace MetaFox\Group\Http\Requests\v1\Request;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AcceptMemberRequest.
 */
class AcceptMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'group_id' => ['required', 'numeric', 'exists:groups,id'],
            'user_id'  => ['required', 'numeric', 'exists:user_entities,id'],
        ];
    }
}
