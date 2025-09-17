<?php

namespace MetaFox\Localize\Http\Requests\v1\Currency\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * Class IndexRequest.
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q'     => ['sometimes', 'nullable', 'string'],
            'page'  => ['sometimes', 'int', 'min:1'],
            'limit' => ['sometimes', 'int', new PaginationLimitRule()],
        ];
    }
}
