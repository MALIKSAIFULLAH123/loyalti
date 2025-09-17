<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Foundation\Http\FormRequest;

class TranslateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'     => ['required', 'integer', 'exists:activity_feeds,id'],
            'target' => ['sometimes', 'string'],
        ];
    }
}
