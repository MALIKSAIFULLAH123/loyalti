<?php

namespace MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo;

use Illuminate\Foundation\Http\FormRequest;

class GetLiveByStreamKeyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'stream_key' => ['required', 'string'],
        ];
    }
}
