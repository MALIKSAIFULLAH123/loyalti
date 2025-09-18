<?php

namespace MetaFox\Page\Http\Requests\v1\PageMember;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class DeletePageAdminRequest.
 */
class DeletePageAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page_id' => ['required', 'numeric', 'exists:pages,id'],
            'user_id' => ['required', 'numeric', 'exists:user_entities,id'],
            'is_delete' => ['required', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
