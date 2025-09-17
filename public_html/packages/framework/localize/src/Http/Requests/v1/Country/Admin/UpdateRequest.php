<?php

namespace MetaFox\Localize\Http\Requests\v1\Country\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;

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
        $id = (int) $this->route('country');

        return [
            'country_iso' => [
                'required',
                'string',
                'between:1,2',
                new CaseInsensitiveUnique('core_countries', 'country_iso', $id),
            ],
            'name' => ['required', 'string'],
        ];
    }
}
