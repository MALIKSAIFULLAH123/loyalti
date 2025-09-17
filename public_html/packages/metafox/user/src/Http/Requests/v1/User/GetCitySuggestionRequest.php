<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Localize\Support\CountryCity as CountryCitySupport;

/**
 * Class GetCitySuggestionRequest.
 */
class GetCitySuggestionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q'         => ['sometimes', 'nullable', 'string'],
            'country'   => ['sometimes', 'string', 'min:2'],
            'state'     => ['sometimes', 'string'],
            'city_code' => ['sometimes', 'string', 'nullable'],
            'limit'     => ['sometimes', 'integer', 'between:1,20'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = CountryCitySupport::CITY_SUGGESTION_LIMIT;
        }

        if (!isset($data['q'])) {
            $data['q'] = '';
        }

        return $data;
    }
}
