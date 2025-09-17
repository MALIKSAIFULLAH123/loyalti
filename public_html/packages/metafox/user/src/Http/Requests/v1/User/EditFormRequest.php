<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class EditFormRequest.
 */
class EditFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.$user.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'resolution' => ['string', 'sometimes', 'nullable', new AllowInRule(['web', 'mobile'])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }
}
