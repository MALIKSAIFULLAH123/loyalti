<?php

namespace MetaFox\Quiz\Http\Requests\v1\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class ViewIndividualPlay extends FormRequest
{
    public function rules(): array
    {
        return [
            'quiz_id' => ['required', 'numeric', 'min:1', 'exists:quizzes,id'],
            'user_id' => ['required', 'numeric', 'min:1', 'exists:user_entities,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        return $data;
    }
}
