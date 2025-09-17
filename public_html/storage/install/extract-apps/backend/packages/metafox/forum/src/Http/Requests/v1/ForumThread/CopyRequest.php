<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Forum\Support\Facades\ForumThread;
use MetaFox\Platform\Facades\Settings;

class CopyRequest extends FormRequest
{
    public function rules(): array
    {
        $maxTitleLength = Settings::get('forum.maximum_name_length', ForumThread::getDefaultMaximumTitleLength());
        $minTitleLength = Settings::get('forum.minimum_name_length', ForumThread::getDefaultMinimumTitleLength());

        return [
            'forum_id'  => ['required', 'numeric', 'exists:forums,id'],
            'title'     => ['required', 'string', 'between: ' . $minTitleLength . ',' . $maxTitleLength],
            'thread_id' => ['required', 'numeric', 'exists:forum_threads,id'],
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
        $maxTitleLength = Settings::get('forum.maximum_name_length', ForumThread::getDefaultMaximumTitleLength());
        $minTitleLength = Settings::get('forum.minimum_name_length', ForumThread::getDefaultMinimumTitleLength());

        return [
            'forum_id.numeric' => __p('forum::validation.please_choose_a_forum'),
            'forum_id.exists'  => __p('forum::validation.please_choose_a_forum'),
            'title.required'   => __p('core::validation.name.required'),
            'title.string'     => __p('core::validation.name.required'),
            'title.between'    => __p('core::validation.name.length_between', [
                'min' => $minTitleLength,
                'max' => $maxTitleLength,
            ]),
        ];
    }
}
