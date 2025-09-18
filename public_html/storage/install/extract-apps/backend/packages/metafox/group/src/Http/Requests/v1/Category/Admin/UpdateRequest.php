<?php

namespace MetaFox\Group\Http\Requests\v1\Category\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Localize\Support\Traits\TranslatableCategoryRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    use TranslatableCategoryRequest;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('category');

        return array_merge($this->getCategoryNameRule(), [
            'name_url'  => ['sometimes', 'string', 'between:3,255', "unique:group_categories,name_url,$id,id"],
            'parent_id' => ['sometimes', 'nullable', 'numeric', 'exists:group_categories,id'],
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
