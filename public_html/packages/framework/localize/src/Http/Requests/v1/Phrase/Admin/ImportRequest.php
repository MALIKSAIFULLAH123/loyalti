<?php

namespace MetaFox\Localize\Http\Requests\v1\Phrase\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ImportRequest.
 */
class ImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
        ];
    }
}
