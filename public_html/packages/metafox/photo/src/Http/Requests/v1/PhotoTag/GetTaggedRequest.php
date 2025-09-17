<?php

namespace MetaFox\Photo\Http\Requests\v1\PhotoTag;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreRequest.
 */
class GetTaggedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'numeric', 'exists:photos,id'],
        ];
    }
}
