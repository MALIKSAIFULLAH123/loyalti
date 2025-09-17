<?php

namespace MetaFox\Quiz\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;

class QuizResultAnswerRule implements RuleContract
{
    protected int $quizId;

    protected string $message;

    public function __construct(int $quizId)
    {
        $this->quizId = $quizId;
        $this->message = '';
    }

    /**
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        $questionIds = array_keys($value);
        $answerIds = array_values($value);

        // Validate the question ids exists and valid
        $validator = Validator::make($questionIds, [
            '*' => [
                'numeric',
                'min:1',
                Rule::exists(Question::class, 'id')->where('quiz_id', $this->quizId),
            ],
        ]);

        if ($validator->fails()) {
            $this->setMessage(__p('quiz::validation.the_question_ids_are_not_valid'));

            return false;
        }

        // Validate the answers ids exists and valid
        $validator = Validator::make($answerIds, [
            '*' => [
                'numeric',
                'min:1',
                Rule::exists(Answer::class, 'id')->whereIn('question_id', $questionIds),
            ],
        ]);

        if ($validator->fails()) {
            $this->setMessage(__p('quiz::validation.the_answer_ids_are_not_valid'));

            return false;
        }

        // Validate if all the questions have been answered
        $quiz = Quiz::query()->withCount('questions')->where('id', $this->quizId)->get();
        $countAnswers = count($value);
        $isEqual = $quiz->filter(function (Quiz $item) use ($countAnswers) {
            return $item->questions_count == $countAnswers;
        })->isNotEmpty();

        if (!$isEqual) {
            $this->setMessage(__p('quiz::validation.all_the_quiz_questions_are_not_answered_yet'));

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @param  string               $message
     * @return QuizResultAnswerRule
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
