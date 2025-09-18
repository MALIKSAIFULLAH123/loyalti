<?php

namespace MetaFox\Like\Http\Requests\v1\Like;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DeleteRequest.
 */
class DeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'item_id'   => ['required', 'numeric'],
            'item_type' => ['required', 'string'],
        ];
    }
}
