<?php

namespace MetaFox\Like\Http\Requests\v1\Reaction;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Storage\Rules\MaxFileUpload;

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
            'title'     => ['required', 'string', 'min:1'],
            'icon'      => ['required', 'image', new MaxFileUpload()],
            'color'     => ['sometimes', 'string'],
            'ordering'  => ['sometimes', 'numeric'],
            'is_active' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
