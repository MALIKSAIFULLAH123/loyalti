<?php

namespace MetaFox\Report\Http\Requests\v1\ReportReason;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'     => ['required', 'string', 'between:3,255'],
            'ordering' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
