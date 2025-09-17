<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumPost;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

class PosterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'thread_id' => ['required', 'numeric', new ExistIfGreaterThanZero('exists:forum_threads,id')],
        ];
    }
}
