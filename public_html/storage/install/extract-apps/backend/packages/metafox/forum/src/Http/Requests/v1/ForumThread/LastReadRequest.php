<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Foundation\Http\FormRequest;

class LastReadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'post_id'   => ['required', 'numeric', 'min:1'],
        ];
    }
}
