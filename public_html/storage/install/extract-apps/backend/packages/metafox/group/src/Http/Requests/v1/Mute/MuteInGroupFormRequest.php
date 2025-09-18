<?php

namespace MetaFox\Group\Http\Requests\v1\Mute;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class MuteInGroupFormRequest.
 */
class MuteInGroupFormRequest extends FormRequest
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
            'user_id'  => ['required', 'numeric', 'exists:group_members,user_id'],
        ];
    }
}
