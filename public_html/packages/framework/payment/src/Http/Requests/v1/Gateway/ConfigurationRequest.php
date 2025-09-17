<?php

namespace MetaFox\Payment\Http\Requests\v1\Gateway;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class ConfigurationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gateway_id' => ['required', 'numeric', 'exists:payment_gateway,id'],
            'value'      => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'value')) {
            Arr::set($data, 'value', null);
        }

        return $data;
    }
}
