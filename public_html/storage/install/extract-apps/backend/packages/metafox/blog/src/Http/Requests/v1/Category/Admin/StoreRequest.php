<?php

namespace MetaFox\Blog\Http\Requests\v1\Category\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Localize\Support\Traits\TranslatableCategoryRequest;

/**
 * Class StoreRequest.
 * @ignore
 * @codeCoverageIgnore
 */
class StoreRequest extends FormRequest
{
    use TranslatableCategoryRequest;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return array_merge($this->getCategoryNameRule(), [
            'name_url'  => ['sometimes', 'string', 'between:3,255', 'unique:blog_categories,name_url'],
            'is_active' => ['sometimes', 'numeric'],
            'ordering'  => ['sometimes', 'numeric'],
            'parent_id' => ['nullable', 'numeric', 'exists:blog_categories,id'],
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->extractCategoryNameData($data);

        Arr::add($data, 'is_active', 0);

        return $data;
    }
}
