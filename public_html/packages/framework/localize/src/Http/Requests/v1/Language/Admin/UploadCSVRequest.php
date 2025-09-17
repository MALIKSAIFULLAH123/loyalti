<?php

namespace MetaFox\Localize\Http\Requests\v1\Language\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ImportRequest.
 */
class UploadCSVRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv'],
        ];
    }
}
