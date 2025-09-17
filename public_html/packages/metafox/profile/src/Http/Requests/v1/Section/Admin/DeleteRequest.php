<?php

namespace MetaFox\Profile\Http\Requests\v1\Section\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class DeleteRequest.
 */
class DeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'migrate_items'  => ['required', 'numeric', new AllowInRule([0, 1])],
            'new_section_id' => ['required_if:migrate_items,1', 'nullable', 'numeric', 'exists:user_custom_sections,id'],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::get($data, 'migrate_items')) {
            Arr::forget($data, 'new_section_id');
        }

        if (!isset($data['new_section_id'])) {
            $data['new_section_id'] = 0;
        }

        return $data;
    }

    /**
     * @return array<mixed>
     */
    public function messages(): array
    {
        return [
            'new_section_id.required_if' => __p('validation.field_is_a_required_field', [
                'field' => __p('profile::phrase.group'),
            ]),
        ];
    }
}
