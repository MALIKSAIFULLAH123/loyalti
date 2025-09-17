<?php

namespace MetaFox\Activity\Http\Requests\v1\Type\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'               => ['sometimes', 'nullable', 'string'],
            'module_id'       => ['sometimes', 'nullable', 'string'],
            'is_active'       => ['sometimes', 'nullable', 'numeric', new AllowInRule([1, 0])],
            'can_create_feed' => ['sometimes', 'nullable', 'numeric', new AllowInRule([1, 0])],
            'page'            => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'           => ['sometimes', 'nullable', 'integer', new PaginationLimitRule(1, 100)],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'limit', 10);

        return $data;
    }
}
