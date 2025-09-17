<?php

namespace MetaFox\Saved\Http\Requests\v1\SavedListMember;

use Illuminate\Foundation\Http\FormRequest;

class RemoveMemberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'numeric', 'exists:saved_list_members,user_id'],
        ];
    }
}
