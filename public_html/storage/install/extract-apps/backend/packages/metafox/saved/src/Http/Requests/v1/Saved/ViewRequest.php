<?php

namespace MetaFox\Saved\Http\Requests\v1\Saved;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class ViewRequest.
 */
class ViewRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'saved_id'      => ['required', 'numeric', 'exists:saved_items,id'],
            'status'        => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'collection_id' => ['sometimes', 'numeric', 'nullable', 'exists:saved_lists,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated();
    }

    protected function prepareForValidation()
    {
        $id = $this->route('id');

        $this->merge([
            'saved_id' => $id,
        ]);
    }

    public function messages()
    {
        return [
            'collection_id.exists' => __p('saved::validation.collection_not_exists'),
        ];
    }
}
