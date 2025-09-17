<?php

namespace MetaFox\Chat\Http\Requests\v1\Room;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

class MarkAllReadRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'room_ids' => ['sometimes', 'array'],
            'room_ids.*' => ['numeric', new ExistIfGreaterThanZero('exists:chat_rooms,id')],
        ];

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['room_ids'])) {
            $data['room_ids'] = [];
        }

        return $data;
    }
}
