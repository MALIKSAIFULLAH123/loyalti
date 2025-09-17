<?php

namespace MetaFox\Quiz\Http\Requests\v1\Result;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Quiz\Rules\QuizResultAnswerRule;
use MetaFox\Quiz\Rules\QuizResultQuizIdRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Quiz\Http\Controllers\Api\v1\ResultController::store;
 * stub: /packages/requests/api_action_request.stub
 */

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
        $context = user();
        $quizId  = $this->input('quiz_id', 0);

        return [
            'quiz_id' => ['required', 'numeric', 'min:1', new QuizResultQuizIdRule($context)],
            'answers' => ['required', 'array', 'min:1', new QuizResultAnswerRule((int) $quizId)],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => __p('quiz::phrase.please_answer_all_questions'),
        ];
    }
}
