<?php

namespace MetaFox\Friend\Http\Requests\v1\FriendRequest;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Friend\Rules\FriendRequestActionRule;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'action' => ['required', 'string', new FriendRequestActionRule()],
        ];
    }
}
