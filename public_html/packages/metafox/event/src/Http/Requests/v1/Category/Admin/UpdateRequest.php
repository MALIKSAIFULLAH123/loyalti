<?php

namespace MetaFox\Event\Http\Requests\v1\Category\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Localize\Support\Traits\TranslatableCategoryRequest;

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
            'name_url'  => ['sometimes', 'between:3,255', "unique:event_categories,name_url,$id,id"],
            'is_active' => ['sometimes', 'numeric'],
            'ordering'  => ['sometimes', 'numeric'],
            'parent_id' => ['nullable', 'numeric', 'exists:event_categories,id'],
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return $this->extractCategoryNameData($data);
    }
}
