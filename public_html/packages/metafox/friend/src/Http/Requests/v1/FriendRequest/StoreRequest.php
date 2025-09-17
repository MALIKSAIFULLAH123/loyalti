<?php

namespace MetaFox\Friend\Http\Requests\v1\FriendRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'friend_user_id' => ['required', 'numeric', 'exists:user_entities,id'],
        ];
    }
}
