<?php

namespace MetaFox\Event\Http\Requests\v1\Setting;

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
    public function rules()
    {
        return [
            'pending_mode' => ['required', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
