<?php

namespace MetaFox\Advertise\Http\Requests\v1\Sponsor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTotalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'item_type'  => ['required', 'string'],
            'item_ids'   => ['required', 'array'],
            'item_ids.*' => ['required_with:item_ids', 'numeric'],
        ];
    }
}
