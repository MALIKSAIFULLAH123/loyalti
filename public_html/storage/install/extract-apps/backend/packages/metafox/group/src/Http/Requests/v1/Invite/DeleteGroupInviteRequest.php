<?php

namespace MetaFox\Group\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * Class DeleteGroupInviteRequest.
 */
class DeleteGroupInviteRequest extends FormRequest
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
            'user_id'  => ['required', 'numeric',new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];
    }
}
