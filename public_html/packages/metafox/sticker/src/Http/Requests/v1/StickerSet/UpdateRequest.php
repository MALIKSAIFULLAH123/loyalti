<?php

namespace MetaFox\Sticker\Http\Requests\v1\StickerSet;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @deprecated
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title'               => ['sometimes', 'string', 'between:3,255'],
            'sticker_temp_file'   => ['sometimes', 'array'],
            'sticker_temp_file.*' => ['numeric'],
        ];
    }
}
