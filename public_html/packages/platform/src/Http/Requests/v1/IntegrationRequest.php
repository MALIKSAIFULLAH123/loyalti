<?php

namespace MetaFox\Platform\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * Class IntegrationRequest.
 */
class IntegrationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'poll_id' => ['sometimes', 'nullable', 'numeric', 'exists:polls,id'],
            'is_edit' => ['sometimes', 'nullable'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'is_edit', filter_var(Arr::get($data, 'is_edit'), FILTER_VALIDATE_BOOLEAN));

        return $data;
    }
}
