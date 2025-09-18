<?php

namespace MetaFox\Page\Http\Requests\v1\PageMember;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Page\Models\PageMember;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'member_type' => ['required', 'numeric', new AllowInRule([PageMember::MEMBER, PageMember::ADMIN])],
            'user_id'     => ['required', 'numeric', 'exists:user_entities,id'],
        ];
    }
}
