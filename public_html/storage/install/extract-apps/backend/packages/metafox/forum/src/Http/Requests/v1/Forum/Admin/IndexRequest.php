<?php

namespace MetaFox\Forum\Http\Requests\v1\Forum\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'parent_id' => ['sometimes', 'numeric', 'exists:forums,id'],
            'q'         => ['sometimes', 'nullable', 'string'],
        ];
    }
}
