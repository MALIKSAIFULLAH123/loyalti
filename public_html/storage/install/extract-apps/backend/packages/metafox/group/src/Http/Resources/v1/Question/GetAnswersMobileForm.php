<?php

namespace MetaFox\Group\Http\Resources\v1\Question;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as ModelEloquent;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Http\Requests\v1\Question\GetAnswersRequest;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Repositories\QuestionRepositoryInterface;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/create_form.stub.
 */

/**
 * Class GetAnswersMobileForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetAnswersMobileForm extends AbstractForm
{
    /**
     * @var int
     */
    protected int $groupId;

    /**
     * @var ModelEloquent
     */
    protected ModelEloquent $request;
    protected array $params;
    protected Collection $collection;

    /**
     * @throws AuthenticationException
     */
    public function boot(GetAnswersRequest $request): void
    {
        $context          = user();
        $this->params     = $request->validated();
        $requestId        = Arr::get($this->params, 'request_id');
        $this->groupId    = Arr::get($this->params, 'group_id');
        $this->request    = $this->requestRepository()->getModel()->newQuery()->find($requestId);
        $this->collection = $this->questionRepository()->getAnswersByRequestId($context, $this->request);
    }

    protected function prepare(): void
    {
        $this->title(__p('group::phrase.membership_questions'))
            ->setValue($this->transformValue($this->collection));
    }

    /**
     * @return QuestionRepositoryInterface
     */
    private function questionRepository(): QuestionRepositoryInterface
    {
        return resolve(QuestionRepositoryInterface::class);
    }

    /**
     * @return RequestRepositoryInterface
     */
    private function requestRepository(): RequestRepositoryInterface
    {
        return resolve(RequestRepositoryInterface::class);
    }

    protected function initialize(): void
    {
        $this->addHeader(['showRightHeader' => false])
            ->component('FormHeader');

        $basic = $this->addBasic();

        $basic->addField(
            Builder::hidden('group_id')->setValue($this->groupId)
        );

        $questionFields = $this->transformQuestions($this->collection);

        if (!empty($questionFields)) {
            $basic->addFields(...$questionFields);
        }
    }

    /**
     * @param Collection $questions
     *
     * @return array<int, mixed>
     */
    private function transformQuestions(Collection $questions): array
    {
        $fields = [];

        foreach ($questions as $key => $question) {
            /** @var Question $question */
            $field = $this->getField($question);
            if (null != $field) {
                $field->setAttribute('order', $key + 1)
                    ->setAttribute('hasFormOrder', true);
                $fields[] = $field;
            }
        }

        return $fields;
    }

    private function transformValue(Collection $questions): array
    {
        $fields = [];

        foreach ($questions as $key => $question) {
            /** @var Question $question */
            $answer = $question->answers;
            $name   = 'question' . MetaFoxConstant::NESTED_ARRAY_SEPARATOR . "question_{$question->entityId()}";
            $value  = $answer->where('question_id', $question->entityId())
                ->where('request_id', $this->request->entityId())
                ->pluck('value')
                ->toArray();

            $fields[$name] = $value;
        }

        return $fields;
    }

    /**
     * @param Question $question
     *
     * @return AbstractField|null
     */
    private function getField(Question $question): ?AbstractField
    {
        $name  = 'question' . MetaFoxConstant::NESTED_ARRAY_SEPARATOR . "question_{$question->entityId()}";
        $label = $question->question;
        $field = null;

        switch ($question->type_id) {
            case Question::TYPE_TEXT:
                $field = Builder::text($name)
                    ->label($label)
                    ->fullWidth()
                    ->disabled()
                    ->setAttribute('titleConfig', [
                        'sx' => [
                            'color'      => 'text.secondary',
                            'fontWeight' => 700,
                        ],
                    ]);
                break;

            case Question::TYPE_SELECT:
                $options = $this->getQuestionOptions($question);

                $field = Builder::radioGroup($name)
                    ->label($label)
                    ->disabled()
                    ->fullWidth()
                    ->options($options)
                    ->setAttribute('titleConfig', [
                        'sx' => [
                            'color'      => 'text.secondary',
                            'fontWeight' => 700,
                        ],
                    ]);
                break;

            case Question::TYPE_MULTI_SELECT:
                $options = $this->getQuestionOptions($question);
                $field   = Builder::checkboxGroup($name)
                    ->label($label)
                    ->disabled()
                    ->fullWidth()
                    ->options($options)
                    ->setAttribute('titleConfig', [
                        'sx' => [
                            'color' => 'text.secondary',
                        ],
                    ]);
                break;
        }

        return $field;
    }

    /**
     * @param Question $question
     *
     * @return array<int, mixed>
     */
    private function getQuestionOptions(Question $question): array
    {
        $options = [];

        foreach ($question->questionFields as $field) {
            $options[] = [
                'label' => $field['title'],
                'value' => (string) $field['id'],
            ];
        }

        return $options;
    }
}
