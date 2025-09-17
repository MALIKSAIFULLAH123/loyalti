<?php

namespace MetaFox\Platform\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class PendingModeRequest.
 */
class PendingModeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pending_mode' => ['required', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
