<?php

namespace MetaFox\Localize\Http\Requests\v1\CountryChild\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DeleteAllRequest.
 */
class DeleteAllRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country_id'  => ['required_without:country_iso', 'numeric'],
            'country_iso' => ['required_without:country_id', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        return $data;
    }
}
