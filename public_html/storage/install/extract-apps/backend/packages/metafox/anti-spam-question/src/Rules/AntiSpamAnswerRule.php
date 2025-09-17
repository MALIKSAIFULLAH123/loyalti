<?php

namespace MetaFox\AntiSpamQuestion\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\AntiSpamQuestion\Models\Question;

class AntiSpamAnswerRule implements Rule
{
    protected ?Question $question = null;

    public function __construct(?Question $question = null)
    {
        $this->question = $question;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!$this->question) {
            return true;
        }

        if (!$this->question->is_active) {
            return true;
        }

        $answers = $this->question->answers()
            ->pluck('answer')
            ->toArray();

        if (empty($answers)) {
            return true;
        }

        if ($this->question->is_case_sensitive) {
            return in_array($value, $answers);
        }

        return in_array(strtolower($value), array_map('strtolower', $answers));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __p('antispamquestion::validation.the_answer_is_incorrect');
    }
}
