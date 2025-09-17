<?php
namespace MetaFox\Photo\Http\Requests\v1\PhotoGroup;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'media_id' => ['sometimes', 'integer', 'exists:photo_group_items,item_id'],
            'limit'    => ['sometimes', 'integer', 'min:1', 'max:20'],
            'page'     => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
