<?php

namespace MetaFox\Event\Http\Requests\v1\Member;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'event_id'    => ['required', 'numeric', 'exists:events,id'],
            'invite_code' => ['sometimes', 'string'],
        ];
    }
}
