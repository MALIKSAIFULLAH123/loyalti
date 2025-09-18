<?php

namespace MetaFox\Sticker\Http\Requests\v1\StickerSet;

use Illuminate\Foundation\Http\FormRequest;

class AddUserSetRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'numeric', 'exists:sticker_sets,id'],
        ];
    }
}
