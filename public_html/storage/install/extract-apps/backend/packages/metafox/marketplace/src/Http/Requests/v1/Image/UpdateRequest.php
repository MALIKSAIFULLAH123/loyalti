<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Image;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        //todo: most of these below attributes is cloned from v4, highly change to adapt v5
        return [
            'files' => ['required'],
        ];
    }
}
