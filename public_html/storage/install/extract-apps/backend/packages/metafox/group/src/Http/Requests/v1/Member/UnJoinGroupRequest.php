<?php

namespace MetaFox\Group\Http\Requests\v1\Member;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class StoreRequest.
 */
class UnJoinGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'not_invite_again'  => ['sometimes', new AllowInRule([0, 1])],
            'reassign_owner_id' => ['sometimes', 'exists:user_entities,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['not_invite_again'])) {
            $data['not_invite_again'] = 0;
        }

        if (!isset($data['reassign_owner_id'])) {
            $data['reassign_owner_id'] = null;
        }
        return $data;
    }
}
