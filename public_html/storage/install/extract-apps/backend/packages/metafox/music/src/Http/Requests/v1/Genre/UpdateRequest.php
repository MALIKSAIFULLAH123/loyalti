<?php

namespace MetaFox\Music\Http\Requests\v1\Genre;

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
    public function rules()
    {
        $id = $this->route('category');

        return array_merge($this->getCategoryNameRule(), [
            'name_url'  => ['sometimes', 'between:3,255', "unique:music_genres,name_url,$id,id"],
            'is_active' => ['sometimes', 'numeric'],
            'ordering'  => ['sometimes', 'numeric'],
            'parent_id' => ['nullable', 'numeric', 'exists:music_genres,id'],
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
