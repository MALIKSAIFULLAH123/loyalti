<?php

namespace MetaFox\Event\Http\Requests\v1\Invite;

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
            'event_id'   => ['required', 'numeric', 'exists:events,id'],
            'user_ids'   => ['required_without:users', 'array'],
            'user_ids.*' => ['numeric', 'exists:user_entities,id'],
            'users'      => ['sometimes', 'array'],
            'users.*.id' => ['numeric', 'exists:user_entities,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $users = Arr::get($data, 'users');

        if ($users) {
            $ids  = collect($users)->pluck('id')->toArray();
            $data = Arr::add($data, 'user_ids', $ids);
        }

        return $data;
    }
}
