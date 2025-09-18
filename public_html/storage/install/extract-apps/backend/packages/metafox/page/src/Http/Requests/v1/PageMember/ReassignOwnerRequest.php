<?php

namespace MetaFox\Page\Http\Requests\v1\PageMember;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

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
            'page_id'    => ['required', 'numeric', 'exists:pages,id'],
            'user_id'    => ['required_without:users', 'numeric', 'exists:user_entities,id'],
            'users'      => ['sometimes', 'nullable', 'array'],
            'users.*'    => ['sometimes', 'nullable', 'array'],
            'users.*.id' => ['required_with:users', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (Arr::has($data, 'user_id')) {
            return $data;
        }

        /** @var Arrayable<int, mixed> $users */
        $users           = Arr::get($data, 'users', []);
        $data['user_id'] = collect($users)->pluck('id')->first();

        return $data;
    }
}
