<?php

namespace MetaFox\Group\Http\Requests\v1\Group;

use Illuminate\Foundation\Http\FormRequest;

/**
 * class RuleConfirmationRequest.
 */
class AnswerQuestionConfirmationRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'group_id'                      => ['required', 'integer', 'exists:groups,id'],
            'is_answer_membership_question' => ['required', 'boolean'],
        ];
    }
}
