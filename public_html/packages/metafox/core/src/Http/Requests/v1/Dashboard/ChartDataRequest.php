<?php

namespace MetaFox\Core\Http\Requests\v1\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class ChartDataRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'   => ['sometimes', 'string', 'nullable'],
            'period' => ['sometimes', 'string', 'nullable'],
        ];
    }
}
