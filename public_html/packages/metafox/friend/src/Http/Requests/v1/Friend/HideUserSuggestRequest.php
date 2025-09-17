<?php

namespace MetaFox\Friend\Http\Requests\v1\Friend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class SuggestRequest.
 */
class HideUserSuggestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'numeric', 'exists:user_entities,id'],
        ];
    }
}
