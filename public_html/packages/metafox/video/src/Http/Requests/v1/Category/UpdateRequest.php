<?php

namespace MetaFox\Video\Http\Requests\v1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Localize\Support\Traits\TranslatableCategoryRequest;

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
            'name_url'  => ['sometimes', 'between:3,255', "unique:video_categories,name_url,$id,id"],
            'is_active' => ['sometimes', 'numeric', 'between:0,1'],
            'ordering'  => ['sometimes', 'numeric', 'min:0'],
            'parent_id' => ['nullable', 'numeric', 'exists:video_categories,id'],
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return $this->extractCategoryNameData($data);
    }
}
