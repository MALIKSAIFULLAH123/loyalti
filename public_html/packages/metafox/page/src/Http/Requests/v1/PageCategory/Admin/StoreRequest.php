<?php

namespace MetaFox\Page\Http\Requests\v1\PageCategory\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Localize\Support\Traits\TranslatableCategoryRequest;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use TranslatableCategoryRequest;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->getCategoryNameRule(), [
            'name_url' => [
                'string',
                'required',
                'regex:/' . MetaFoxConstant::SLUGIFY_REGEX . '/',
                'unique:page_categories,name_url',
            ],
            'parent_id' => ['sometimes', 'integer', 'min:0', 'exists:page_categories,id'],
            'is_active' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'ordering'  => ['sometimes', 'numeric'],
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
