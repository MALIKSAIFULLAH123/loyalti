<?php

namespace MetaFox\Page\Http\Requests\v1\Block;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'page_id'           => ['required', 'numeric', 'exists:pages,id'],
            'user_id'           => ['required', 'numeric', 'exists:users,id'],
            'delete_activities' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
