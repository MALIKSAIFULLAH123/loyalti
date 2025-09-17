<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class MoveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'forum_id' => ['required', 'numeric', 'exists:forums,id'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        if (!Arr::has($data, 'forum_id')) {
            Arr::set($data, 'forum_id', 0);
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'forum_id.required' => __p('forum::validation.please_choose_a_forum'),
            'forum_id.numeric'  => __p('forum::validation.please_choose_a_forum'),
            'forum_id.exists'   => __p('forum::validation.please_choose_a_forum'),
        ];
    }
}
