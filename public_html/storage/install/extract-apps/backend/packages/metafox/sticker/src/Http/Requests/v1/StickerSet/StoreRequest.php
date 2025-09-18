<?php

namespace MetaFox\Sticker\Http\Requests\v1\StickerSet;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

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
            'title'               => ['required', 'string', 'between:3,255'],
            'is_active'           => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'sticker_temp_file'   => ['sometimes', 'array'],
            'sticker_temp_file.*' => ['numeric'],
        ];
    }
}
