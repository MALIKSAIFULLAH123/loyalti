<?php

namespace MetaFox\Friend\Http\Requests\v1\FriendList;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * Class ActionFriendListRequest.
 */
class ManageFriendListRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'users' => ['array'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $users = Arr::get($data, 'users', []);
        $data['user_ids'] = collect($users)->pluck('id')->toArray();

        return $data;
    }
}
