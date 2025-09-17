<?php

namespace MetaFox\Chat\Http\Requests\v1\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'member_ids'   => ['sometimes', 'array'],
            'member_ids.*' => ['numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'users'        => ['sometimes', 'array'],
            'users.*'      => ['sometimes', 'array'],
            'users.*.id'   => ['numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $users = Arr::get($data, 'users');

        if ($users) {
            $ids  = collect($users)->pluck('id')->toArray();
            $data = Arr::add($data, 'member_ids', $ids);
        }

        return $data;
    }
}
