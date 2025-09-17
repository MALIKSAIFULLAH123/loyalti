<?php

namespace MetaFox\Core\Http\Requests\v1\StatsContentType\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
    public function rules()
    {
        return [
            'icon' => ['required', 'string'],
        ];
    }
}
