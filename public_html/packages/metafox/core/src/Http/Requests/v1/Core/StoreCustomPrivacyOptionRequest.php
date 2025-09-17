<?php

namespace MetaFox\Core\Http\Requests\v1\Core;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;

class StoreCustomPrivacyOptionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:' . Settings::get('friend.maximum_name_length', 64)],
        ];

        return $this->handleFriendRule($rules);
    }

    protected function handleFriendRule(array &$rules): array
    {
        $context = user();

        if (!$context->hasPermissionTo('friend_list.update')) {
            return $rules;
        }

        $rules['users'] = ['sometimes', 'nullable', 'array'];

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'users')) {
            return $data;
        }

        $users = Arr::get($data, 'users', []);
        $data['user_ids'] = collect($users)->pluck('id')->toArray();

        Arr::forget($data, 'users');
        return $data;
    }
}
