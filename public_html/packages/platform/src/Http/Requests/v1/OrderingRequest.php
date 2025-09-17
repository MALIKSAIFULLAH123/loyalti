<?php

namespace MetaFox\Platform\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * Class OrderingRequest.
 */
class OrderingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'orders'   => ['required', 'array'],
            'orders.*' => ['numeric'],
        ];
    }
}
