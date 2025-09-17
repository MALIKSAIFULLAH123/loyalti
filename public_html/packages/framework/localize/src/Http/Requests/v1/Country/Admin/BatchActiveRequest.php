<?php

namespace MetaFox\Localize\Http\Requests\v1\Country\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class MassActiveRequest.
 */
class BatchActiveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id'     => ['required', 'array'],
            'active' => ['required', 'numeric', 'in:0,1'],
        ];
    }
}
