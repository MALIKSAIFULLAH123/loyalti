<?php

namespace MetaFox\Poll\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;

class PollResultAnswersRule implements RuleContract
{
    private int $pollId;
    private string $message = '';

    public function __construct(int $pollId)
    {
        $this->pollId = $pollId;
    }

    /**
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        $validator = Validator::make($value, [
            '*' => [
                'numeric',
                'min:1',
                Rule::exists(Answer::class, 'id')->where('poll_id', $this->pollId),
            ],
        ]);

        if ($validator->fails()) {
            $this->setMessage(__p('poll::validation.the_answers_you_chose_are_not_existed'));

            return false;
        }

        //Poll must exist at first before validate answers
        /** @var Poll $poll */
        $poll = Poll::query()->withCount('answers')->where('id', $this->pollId)->first();

        if (empty($poll)) {
            $this->setMessage(__p('poll::validation.the_owner_just_wanted'));

            return false;
        }

        if (!$poll->is_multiple) {
            if (1 < count($value)) {
                $this->setMessage(__p('poll::validation.the_quantity_of_answers_you_ve_chosen_is_not_valid'));

                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @param  string  $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
