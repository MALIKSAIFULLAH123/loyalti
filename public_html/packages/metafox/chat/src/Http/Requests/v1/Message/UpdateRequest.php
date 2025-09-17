<?php

namespace MetaFox\Chat\Http\Requests\v1\Message;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'message'   => ['sometimes', 'string'],
            'type'      => ['sometimes', 'string', new AllowInRule(['text', 'file', 'delete'])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return $data;
    }
}
