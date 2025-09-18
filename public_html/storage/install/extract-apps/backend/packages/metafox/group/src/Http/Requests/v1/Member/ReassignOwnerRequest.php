<?php

namespace MetaFox\Group\Http\Requests\v1\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * Class ReassignOwnerRequest.
 */
class ReassignOwnerRequest extends FormRequest
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
            'user_id'    => ['required_without:users', 'numeric', 'exists:user_entities,id'],
            'users'      => ['sometimes', 'array', 'nullable'],
            'users.*.id' => ['required_with:users', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (Arr::has($data, 'user_id')) {
            return $data;
        }

        $users           = Arr::get($data, 'users', []);
        $data['user_id'] = collect($users)->pluck('id')->first();

        return $data;
    }
}
