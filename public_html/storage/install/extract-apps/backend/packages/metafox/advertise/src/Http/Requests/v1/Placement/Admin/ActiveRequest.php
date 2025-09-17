<?php

namespace MetaFox\Advertise\Http\Requests\v1\Placement\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;

class ActiveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'active' => ['required', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = array_merge($data, [
            'active' => (bool) Arr::get($data, 'active', true),
        ]);

        return $data;
    }
}
