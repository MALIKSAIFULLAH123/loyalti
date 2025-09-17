<?php

namespace MetaFox\Forum\Http\Requests\v1\Forum\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Rules\AllowInRule;

class DeleteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'             => ['required', 'numeric', 'exists:forums,id'],
            'delete_option'  => ['required', new AllowInRule([ForumSupport::DELETE_MIGRATION, ForumSupport::DELETE_PERMANENTLY])],
            'alternative_id' => ['required_if:delete_option,' . ForumSupport::DELETE_MIGRATION, 'numeric', 'exists:forums,id'],
        ];
    }
}
