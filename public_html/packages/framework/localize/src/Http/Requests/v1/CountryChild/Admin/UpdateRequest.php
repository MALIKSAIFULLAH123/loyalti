<?php

namespace MetaFox\Localize\Http\Requests\v1\CountryChild\Admin;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id    = $this->route('child');
        $rules = parent::rules();

        return array_merge($rules, [
            'state_iso'     => ['string', 'required', "unique:core_country_states,state_iso,$id,id"],
            'state_code'    => ['numeric', 'required', 'digits_between:1,12', "unique:core_country_states,state_code,$id,id"],
            'geonames_code' => ['sometimes', 'nullable', 'numeric', 'digits_between:1,12', "unique:core_country_states,geonames_code,$id,id"],
            'fips_code'     => ['sometimes', 'nullable', 'string', "unique:core_country_states,fips_code,$id,id"],
        ]);
    }
}
