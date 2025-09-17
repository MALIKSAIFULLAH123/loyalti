<?php

namespace MetaFox\Group\Http\Requests\v1\Group;

use Illuminate\Foundation\Http\FormRequest;

/**
 * class RuleConfirmationRequest.
 */
class RuleConfirmationRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'group_id'             => ['required', 'integer', 'exists:groups,id'],
            'is_rule_confirmation' => ['required', 'boolean'],
        ];
    }
}
