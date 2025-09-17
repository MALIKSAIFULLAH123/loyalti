<?php

namespace MetaFox\Saved\Http\Requests\v1\Saved;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UnSaveRequest.
 */
class UnSaveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id'   => ['required', 'numeric'],
            'item_type' => ['required', 'string'],
        ];
    }
}
