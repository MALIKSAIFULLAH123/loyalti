<?php

namespace MetaFox\Quiz\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MetaFox\Platform\Contracts\User;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;

class QuizResultQuizIdRule implements RuleContract
{
    protected string $message = '';

    public function __construct(protected User $user)
    {
    }

    /**
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        $data = ['quiz_id' => $value];

        // Validate the quiz id exists and valid
        $validator = Validator::make($data, [
            'quiz_id' => [
                Rule::exists(Quiz::class, 'id'),
                Rule::unique(Result::class, 'quiz_id')->where(function ($query) {
                    return $query->where('user_id', $this->user->entityId());
                }),
            ],
        ]);

        if ($validator->fails()) {
            $this->setMessage(__p('quiz::phrase.you_have_completed_this_quiz'));

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
     * @param  string $message
     * @return void
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }
}
