<?php

namespace MetaFox\Friend\Http\Requests\v1\FriendList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AssignFriendListRequest.
 */
class AssignFriendListRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'list_id' => ['array'],
        ];
    }
}
