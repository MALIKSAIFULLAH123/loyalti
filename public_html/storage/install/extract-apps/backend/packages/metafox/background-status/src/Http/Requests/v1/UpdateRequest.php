<?php

namespace MetaFox\BackgroundStatus\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

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
            'title'                  => ['sometimes', 'string', 'between:3,255'],
            'is_active'              => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'is_default'             => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'background_temp_file'   => ['sometimes', 'array'],
            'background_temp_file.*' => ['numeric'],
        ];
    }
}
