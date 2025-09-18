<?php

namespace MetaFox\Group\Http\Requests\v1\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Rules\AnswerQuestion;
use MetaFox\Group\Rules\ConfirmRule;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Platform\MetaFoxConstant;

class StoreAnswerRequest extends FormRequest
{
    /**
     * @var ConfirmRule
     */
    private $confirmRule;

    /**
     * @var AnswerQuestion
     */
    private $answerQuestionRule;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var bool
     */
    private $mustAnswerQuestion;

    /**
     * @var bool
     */
    private $mustConfirmRule;

    public function rules(): array
    {
        $this->group = GroupFacade::getGroup($this->input('group_id', 0));

        $rules = [
            'group_id'     => ['required', 'numeric', 'exists:groups,id'],
            'is_confirmed' => ['sometimes', 'numeric'],
            'question'     => ['sometimes', 'array'],
        ];

        if ($this->mustAnswerMembershipQuestion()) {
            $rules['question'] = ['required', $this->getAnswerQuestionRule($this->group)];
        }

        if ($this->mustConfirmRule()) {
            $rules['is_confirmed'] = ['required', $this->getConfirmRule($this->group)];
        }

        return $rules;
    }

    public function mustConfirmRule()
    {
        if (null === $this->mustConfirmRule) {
            $this->mustConfirmRule = $this->group instanceof Group && GroupFacade::mustAcceptGroupRule($this->group) === true;
        }

        return $this->mustConfirmRule;
    }

    public function setMustConfirmRule(bool $valid)
    {
        $this->mustConfirmRule = $valid;
    }

    public function mustAnswerMembershipQuestion()
    {
        if (null === $this->mustAnswerQuestion) {
            $this->mustAnswerQuestion = $this->group instanceof Group && GroupFacade::mustAnswerMembershipQuestion($this->group) === true;
        }

        return $this->mustAnswerQuestion;
    }

    public function setMustAnswerMembershipQuestion(bool $valid)
    {
        $this->mustAnswerQuestion = $valid;
    }

    public function messages(): array
    {
        return [
            'question.required'     => __p('group::phrase.you_must_answer_all_questions'),
            'is_confirmed.required' => __p('group::phrase.you_must_accept_group_rules'),
        ];
    }

    public function getConfirmRule(?Group $group = null): ?ConfirmRule
    {
        if (null === $this->confirmRule && $group instanceof Group) {
            $this->confirmRule = new ConfirmRule($group);
        }

        return $this->confirmRule;
    }

    public function setConfirmRule(ConfirmRule $rule): void
    {
        $this->confirmRule = $rule;
    }

    public function getAnswerQuestionRule(?Group $group = null): ?AnswerQuestion
    {
        if (null === $this->answerQuestionRule && $group instanceof Group) {
            $this->answerQuestionRule = new AnswerQuestion($group);
        }

        return $this->answerQuestionRule;
    }

    public function setAnswerQuestionRule(AnswerQuestion $rule): void
    {
        $this->answerQuestionRule = $rule;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (Arr::has($data, 'question')) {
            $questions = Arr::get($data, 'question');

            foreach ($questions as $key => $question) {
                if (null === $question) {
                    unset($questions[$key]);
                }

                if (MetaFoxConstant::EMPTY_STRING == $question) {
                    unset($questions[$key]);
                }
            }

            Arr::set($data, 'question', $questions);
        }

        return $data;
    }
}
