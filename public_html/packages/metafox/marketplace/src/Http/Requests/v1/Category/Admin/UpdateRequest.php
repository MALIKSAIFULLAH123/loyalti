<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Category\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Localize\Support\Traits\TranslatableCategoryRequest;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class UpdateRequest.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateRequest extends FormRequest
{
    use TranslatableCategoryRequest;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->route('category');

        return array_merge($this->getCategoryNameRule(true), [
            'name_url' => [
                'string',
                'required',
                'regex:/' . MetaFoxConstant::SLUGIFY_REGEX . '/',
                "unique:marketplace_categories,name_url,$id,id",
            ],
            'is_active' => ['sometimes', 'numeric'],
            'ordering'  => ['sometimes', 'numeric'],
            'parent_id' => ['nullable', 'numeric', 'exists:marketplace_categories,id'],
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return $this->extractCategoryNameData($data);
    }
}
