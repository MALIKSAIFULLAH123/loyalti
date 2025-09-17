<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Foundation\Http\FormRequest;

class MergeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'forum_id'          => ['required', 'numeric', 'exists:forums,id'],
            'current_thread_id' => ['required', 'numeric', 'exists:forum_threads,id'],
            'merged_thread_id'  => ['required', 'numeric', 'exists:forum_threads,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'forum_id.required'         => __p('forum::validation.please_choose_a_forum'),
            'forum_id.numeric'          => __p('forum::validation.please_choose_a_forum'),
            'forum_id.exists'           => __p('forum::validation.please_choose_a_forum'),
            'merged_thread_id.required' => __p('forum::validation.please_choose_a_thread'),
            'merged_thread_id.numeric'  => __p('forum::validation.please_choose_a_thread'),
            'merged_thread_id.exists'   => __p('forum::validation.please_choose_a_thread'),
        ];
    }
}
