<?php

namespace MetaFox\Quiz\Rules;

use Illuminate\Contracts\Validation\Rule;

class MustHaveCorrectAnswer implements Rule
{
    private string $fieldString;
    private string $attribute;

    /**
     * @return string
     */
    public function getFieldString(): string
    {
        return $this->fieldString;
    }

    /**
     * AllowInRule constructor.
     *
     * @param string $field
     */
    public function __construct(string $field)
    {
        $this->fieldString = $field;
    }

    /**
     * Determine if the validation rule passes.
     * Verify the field to have at least 1 correct <field_string>. Sample request as below
     * <code>
     * [
     *     ...
     *     'answers'  => [
     *          [... , 'is_correct' => 1, ... ],
     *          [... , 'is_correct' => 0, ... ],
     *          [... , 'is_correct' => 0, ... ],
     *     ]
     *     ...
     * ]
     * </code>.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;

        if (!is_array($value)) {
            return false;
        }

        $correctAnswerCnt = 0;
        foreach ($value as $answer) {
            // If answer is not an array => not count as a answer
            if (!is_array($answer)) {
                continue;
            }

            if (isset($answer[$this->fieldString]) && $answer[$this->fieldString]) {
                $correctAnswerCnt++;
            }
        }

        if ($correctAnswerCnt < 1) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __p('quiz::validation.you_need_to_set_at_least_one_correct_answer_per_question');
    }
}
