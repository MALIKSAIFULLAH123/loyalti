<?php

namespace MetaFox\Search\Http\Requests\v1\Search;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * Class TrendingRequest.
 */
class TrendingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page'  => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', 10);
        }

        return $data;
    }
}
