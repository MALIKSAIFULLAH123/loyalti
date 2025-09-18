<?php

namespace MetaFox\Page\Http\Requests\v1\PageMember;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
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
        ];
    }
}
