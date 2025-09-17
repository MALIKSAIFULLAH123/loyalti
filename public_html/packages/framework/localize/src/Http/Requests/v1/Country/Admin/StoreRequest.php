<?php

namespace MetaFox\Localize\Http\Requests\v1\Country\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Localize\Models\Country;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;

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
            'country_iso' => [
                'required',
                'string',
                'between:1,2',
                new CaseInsensitiveUnique('core_countries', 'country_iso'),
            ],
            'name' => ['required', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data['ordering'] = Country::count() + 1;

        return $data;
    }
}
