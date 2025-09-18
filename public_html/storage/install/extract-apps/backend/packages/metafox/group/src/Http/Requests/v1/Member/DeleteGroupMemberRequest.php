<?php

namespace MetaFox\Group\Http\Requests\v1\Member;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class DeleteGroupMemberRequest.
 */
class DeleteGroupMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'group_id'          => ['required', 'numeric', 'exists:groups,id'],
            'user_id'           => ['required', 'numeric', 'exists:user_entities,id'],
            'delete_activities' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
