<?php

namespace MetaFox\Group\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

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
    public function rules(): array
    {
        return [
            'group_id'   => ['required', 'numeric', 'exists:groups,id'],
            'user_ids'   => ['required', 'array'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $userIds = Arr::get($data, 'user_ids', []);

        $data['ids'] = collect($userIds)->map(function ($item) {
            if (is_array($item)) {
                return Arr::get($item, 'id', 0);
            }

            return $item;
        })->toArray();

        return $data;
    }
}
