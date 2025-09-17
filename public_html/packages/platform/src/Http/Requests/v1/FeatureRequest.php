<?php

namespace MetaFox\Platform\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class FeatureRequest.
 */
class FeatureRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'feature' => ['required', 'numeric', 'in:0,1'],
        ];
    }
}
