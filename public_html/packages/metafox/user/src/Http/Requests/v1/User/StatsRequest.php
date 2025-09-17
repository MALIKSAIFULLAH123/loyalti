<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;

class StatsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'item_type' => ['required', 'string'],
            'item_id'   => ['required', 'numeric'],
        ];
    }
}
