<?php

namespace MetaFox\Like\Http\Requests\v1\Reaction;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Storage\Rules\MaxFileUpload;

/**
 * Class UpdateRequest.
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
            'title'     => ['sometimes', 'string', 'min:1'],
            'icon'      => ['sometimes', 'image', new MaxFileUpload()],
            'color'     => ['sometimes', 'string'],
            'ordering'  => ['sometimes', 'numeric'],
            'is_active' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
