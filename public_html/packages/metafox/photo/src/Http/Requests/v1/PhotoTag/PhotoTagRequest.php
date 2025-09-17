<?php

namespace MetaFox\Photo\Http\Requests\v1\PhotoTag;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreRequest.
 */
class PhotoTagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id'     => ['required', 'numeric', 'exists:photos,id'],
            'tag_user_id' => ['required', 'numeric', 'exists:user_entities,id'],
            'px'          => ['sometimes', 'numeric', 'min:0'],
            'py'          => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['px'])) {
            $data['px'] = 0;
        }

        if (!isset($data['py'])) {
            $data['py'] = 0;
        }

        return $data;
    }
}
