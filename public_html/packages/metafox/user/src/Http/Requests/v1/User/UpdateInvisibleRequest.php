<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateInvisibleRequest.
 */
class UpdateInvisibleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'invisible' => ['required', 'numeric', 'between:0,1'],
        ];
    }
}
