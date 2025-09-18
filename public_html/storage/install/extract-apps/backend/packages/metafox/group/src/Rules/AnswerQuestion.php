<?php

namespace MetaFox\Group\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Support\Facades\Group as GroupFacade;

/**
 * Class AnswerQuestion.
 * @ignore
 */
class AnswerQuestion implements RuleContract
{
    /**
     * @var Group|null
     */
    private $group;

    public function __construct(?Group $group = null)
    {
        $this->group = $group;
    }

    /**
     * @param  Group $group
     * @return void
     */
    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }

    /**
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (null === $this->group) {
            return false;
        }

        if ($this->mustAnswerQuestion() === false) {
            return true;
        }

        $questions = GroupFacade::getQuestions($this->group);

        foreach ($questions as $question) {
            $key = 'question_' . $question->entityId();

            if (array_key_exists($key, $value) === false || null === $value[$key]) {
                return false;
            }

            $answer = $value[$key];

            switch ($question->type_id) {
                case Question::TYPE_MULTI_SELECT:
                    $granted = !is_array($answer) || count($answer) == 0;
                    break;
                default:
                    $granted = trim($answer) == '';
                    break;
            }

            if ($granted === true) {
                return false;
            }
        }

        return true;
    }

    public function mustAnswerQuestion()
    {
        return GroupFacade::mustAnswerMembershipQuestion($this->group) === true;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('group::phrase.you_must_answer_all_questions');
    }
}
