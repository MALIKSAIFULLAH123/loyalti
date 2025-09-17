<?php

namespace MetaFox\Quiz\Http\Requests\v1\Quiz;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;
use MetaFox\Quiz\Rules\MustHaveCorrectAnswer;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Quiz\Http\Controllers\Api\v1\QuizController::store;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function rules(): array
    {
        $context          = user();
        $fileRequiredRule = $context->hasPermissionTo('quiz.require_upload_photo') ? 'required' : 'sometimes';

        $rules = [
            'title'     => ['required', 'string', new ResourceNameRule('quiz')],
            'text'      => ['required', 'string'],
            'questions' => [
                'required', 'array', 'min:' . $this->getTotalMinimumQuestions(),
            ],
            'questions.*'          => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string', 'between:3,255'],
            'questions.*.answers'  => [
                'required', 'array', 'min:' . $this->getTotalMinimumAnswers(), new MustHaveCorrectAnswer('is_correct'),
            ],
            'questions.*.answers.*'            => ['array'],
            'questions.*.answers.*.answer'     => ['required', 'string', 'between:1,255'],
            'questions.*.answers.*.is_correct' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'owner_id'                         => [
                'sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id'),
            ],
            'file'           => [$fileRequiredRule, 'array'],
            'file.temp_file' => ['required_with:file', 'numeric', 'exists:storage_files,id'],
            'privacy'        => ['required', new PrivacyRule()],
        ];

        if (!$context->hasSuperAdminRole()) {
            $rules['questions'][]           = 'max:' . $this->getTotalMaximumQuestions();
            $rules['questions.*.answers'][] = 'max:' . $this->getTotalMaximumAnswers();
        }

        $rules = $this->applyAttachmentRules($rules);

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data = $this->handlePrivacy($data);

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        if (isset($data['file']['temp_file'])) {
            $data['temp_file'] = $data['file']['temp_file'];
        }

        $questionOrdering = $answerOrdering = 0;

        foreach ($data['questions'] as $k1 => $question) {
            // Set question ordering
            $data['questions'][$k1]['ordering'] = ++$questionOrdering;

            foreach ($question['answers'] as $k2 => $answer) {
                // Set default is_correct as false when it is not set
                if (!isset($answer['is_correct'])) {
                    $data['questions'][$k1]['answers'][$k2]['is_correct'] = 0;
                }

                // Set answer ordering
                $data['questions'][$k1]['answers'][$k2]['ordering'] = ++$answerOrdering;
            }
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'questions.min' => __p(
                'validation.min.array',
                ['attribute' => 'questions', 'min' => $this->getTotalMinimumQuestions()]
            ),
            'questions.max' => __p(
                'validation.min.array',
                ['attribute' => 'questions', 'max' => $this->getTotalMaximumQuestions()]
            ),
            'questions.*.question.between' => __p(
                'validation.between.string',
                ['attribute' => 'question', 'min' => 3, 'max' => 255]
            ),
            'questions.*.answers.array' => __p('validation.array', ['attribute' => 'answers']),
            'questions.*.answers.min'   => __p(
                'validation.min.array',
                ['attribute' => 'answers', 'min' => $this->getTotalMinimumAnswers()]
            ),
            'questions.*.answers.max' => __p(
                'validation.max.array',
                ['attribute' => 'answers', 'max' => $this->getTotalMaximumAnswers()]
            ),
            'questions.*.answers.*.answer.required' => __p('validation.required', ['attribute' => 'answer']),
            'questions.*.answers.*.answer.between'  => __p(
                'validation.between.string',
                ['attribute' => 'answer', 'min' => 1, 'max' => 255]
            ),
            'questions.*.answers.*.is_correct.numeric' => __p('validation.numeric', ['attribute' => 'is_correct']),
            'file.required'                            => __p('quiz::phrase.banner_is_a_required_field'),
        ];
    }

    protected function getTotalMinimumAnswers(): int
    {
        $context = user();

        return (int) $context->getPermissionValue('quiz.min_answer_question_quiz');
    }

    protected function getTotalMaximumAnswers(): int
    {
        $context = user();

        return (int) $context->getPermissionValue('quiz.max_answer_question_quiz');
    }

    protected function getTotalMinimumQuestions(): int
    {
        $context = user();

        return (int) $context->getPermissionValue('quiz.min_question_quiz');
    }

    protected function getTotalMaximumQuestions(): int
    {
        $context = user();

        return (int) $context->getPermissionValue('quiz.max_question_quiz');
    }
}
