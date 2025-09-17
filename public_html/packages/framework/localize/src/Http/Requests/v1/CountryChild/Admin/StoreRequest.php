<?php

namespace MetaFox\Localize\Http\Requests\v1\CountryChild\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'country_iso'   => ['required', 'string', 'exists:core_countries,country_iso'],
            'name'          => ['required', 'string', 'max:255'],
            'state_iso'     => ['string', 'required', 'unique:core_country_states,state_iso'],
            'state_code'    => ['numeric', 'required', 'digits_between:1,12', 'unique:core_country_states,state_code'],
            'geonames_code' => ['sometimes', 'nullable', 'numeric', 'digits_between:1,12', 'unique:core_country_states,geonames_code'],
            'fips_code'     => ['sometimes', 'nullable', 'string', 'unique:core_country_states,fips_code'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data['ordering'] = 0;

        return $data;
    }

    public function messages()
    {
        return [
            'state_iso.required'           => __p('validation.required', [
                'attribute' => __p('localize::phrase.state_iso'),
            ]),
            'name.required'                => __p('validation.required', [
                'attribute' => __p('core::phrase.name'),
            ]),
            'state_code.required'          => __p('validation.required', [
                'attribute' => __p('localize::phrase.state_code'),
            ]),
            'geonames_code.required'       => __p('validation.required', [
                'attribute' => __p('localize::phrase.geonames_code'),
            ]),
            'fips_code.required'           => __p('validation.required', [
                'attribute' => __p('localize::phrase.fips_code'),
            ]),
            'state_iso.unique'             => __p('validation.unique', [
                'attribute' => __p('localize::phrase.state_iso'),
            ]),
            'fips_code.unique'             => __p('validation.unique', [
                'attribute' => __p('localize::phrase.fips_code'),
            ]),
            'state_code.unique'            => __p('validation.unique', [
                'attribute' => __p('localize::phrase.state_code'),
            ]),
            'geonames_code.unique'         => __p('validation.unique', [
                'attribute' => __p('localize::phrase.geonames_code'),
            ]),
            'geonames_code.digits_between' => __p('validation.digits_between', [
                'attribute' => __p('localize::phrase.geonames_code'),
                'min'       => 1,
                'max'       => 12,
            ]),
            'state_code.digits_between'    => __p('validation.digits_between', [
                'attribute' => __p('localize::phrase.state_code'),
                'min'       => 1,
                'max'       => 12,
            ]),
        ];
    }
}
