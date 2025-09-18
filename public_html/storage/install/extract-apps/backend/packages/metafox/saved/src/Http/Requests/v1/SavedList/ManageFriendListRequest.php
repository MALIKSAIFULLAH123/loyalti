<?php

namespace MetaFox\Saved\Http\Requests\v1\SavedList;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

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
            'users'      => ['array'],
            'users.*'    => ['array'],
            'users.*.id' => ['numeric', new ExistIfGreaterThanZero('exists:users,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data             = parent::validated($key, $default);
        $users            = Arr::get($data, 'users', []);
        $data['user_ids'] = collect($users)->pluck('id')->toArray();

        return $data;
    }
}
