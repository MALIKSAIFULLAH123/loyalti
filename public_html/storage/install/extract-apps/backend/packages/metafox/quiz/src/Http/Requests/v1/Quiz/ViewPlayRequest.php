<?php

namespace MetaFox\Quiz\Http\Requests\v1\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class ViewPlayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question_id' => ['required', 'numeric', 'exists:quiz_questions,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        return $data;
    }
}
