<?php

namespace MetaFox\Comment\Http\Requests\v1\Comment;

use Illuminate\Foundation\Http\FormRequest;

class TranslateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'     => ['required', 'integer', 'exists:comments,id'],
            'target' => ['sometimes', 'string'],
        ];
    }
}
