<?php

namespace MetaFox\Core\Http\Requests\v1\Core;

use Illuminate\Foundation\Http\FormRequest;

class UrlToRouteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'url' => ['required'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data['url'] = trim($data['url'], '/');

        return $data;
    }
}
