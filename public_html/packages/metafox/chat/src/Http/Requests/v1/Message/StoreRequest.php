<?php

namespace MetaFox\Chat\Http\Requests\v1\Message;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;

class StoreRequest extends FormRequest
{
    use AttachmentRequestTrait;

    public function rules(): array
    {
        $rules = [
            'room_id'  => ['numeric', 'exists:chat_rooms,id'],
            'message'  => ['sometimes', 'string', 'nullable'],
            'type'     => ['sometimes', 'string', new AllowInRule(['text', 'file', 'delete'])],
            'reply_id' => ['sometimes', 'int', 'exists:chat_messages,id'],
            'tempId'   => ['sometimes', 'string'],
        ];

        return $this->applyAttachmentRules($rules);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['type'])) {
            $data['type'] = 'text';
        }

        if (!isset($data['reply_id'])) {
            $data['reply_id'] = 0;
        }

        return $data;
    }
}
