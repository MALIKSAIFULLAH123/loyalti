<?php

namespace MetaFox\Poll\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use MetaFox\Poll\Models\Poll;

class PollResultPollIdRule implements RuleContract
{
    private string $message = '';

    /**
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        //Poll must exist and must not be closed at this time

        /** @var Poll $poll */
        $poll = Poll::query()->where('id', $value)->first();

        if (empty($poll)) {
            $this->setMessage(__p('poll::validation.the_owner_just_wanted'));

            return false;
        }

        if ($poll->is_closed) {
            $this->setMessage(__p('poll::validation.the_poll_you_voted_for_is_closed'));

            return false;
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
