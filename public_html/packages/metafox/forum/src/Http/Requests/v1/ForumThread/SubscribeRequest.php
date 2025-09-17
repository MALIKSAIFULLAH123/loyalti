<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class SubscribeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_subscribed' => ['required', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        Arr::set($data, 'is_subscribed', !$data['is_subscribed']);

        return $data;
    }
}
