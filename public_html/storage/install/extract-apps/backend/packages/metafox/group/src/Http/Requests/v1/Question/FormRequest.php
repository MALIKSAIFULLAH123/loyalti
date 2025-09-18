<?php

namespace MetaFox\Group\Http\Requests\v1\Question;

use Illuminate\Foundation\Http\FormRequest as MainRequest;

class FormRequest extends MainRequest
{
    public function rules(): array
    {
        return [
            'group_id' => ['sometimes', 'numeric', 'exists:groups,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!array_key_exists('group_id', $data)) {
            $data['group_id'] = 0;
        }

        return $data;
    }
}
