<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class CloseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_closed' => ['required', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        Arr::set($data, 'is_closed', !$data['is_closed']);

        return $data;
    }
}
