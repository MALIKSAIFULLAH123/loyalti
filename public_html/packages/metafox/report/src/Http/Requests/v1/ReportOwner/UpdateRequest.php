<?php

namespace MetaFox\Report\Http\Requests\v1\ReportOwner;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

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
            'keep_post' => ['required', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
