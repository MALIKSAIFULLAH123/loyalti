<?php

namespace MetaFox\Quiz\Http\Requests\v1\Quiz;

use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;
use MetaFox\Quiz\Rules\MustHaveCorrectAnswer;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Quiz\Http\Controllers\Api\v1\QuizController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $context = user();

        $fileRequiredRule = $context->hasPermissionTo('quiz.require_upload_photo') ? 'required' : 'sometimes';

        $rules = [
            'title'                            => ['sometimes', 'string', 'between:3,255'],
            'text'                             => ['sometimes', 'string'],
            'questions'                        => ['required', 'array', 'min:' . $this->getTotalMinimumQuestions()],
            'questions.*.id'                   => ['sometimes', 'numeric', 'exists:MetaFox\Quiz\Models\Question,id'],
            'questions.*.question'             => ['required_with:questions.*', 'string', 'between:3,255'],
            'questions.*.answers'              => ['required', 'array', 'min:' . $this->getTotalMinimumAnswers(), new MustHaveCorrectAnswer('is_correct')],
            'questions.*.answers.*'            => ['array'],
            'questions.*.answers.*.id'         => ['sometimes', 'numeric', 'exists:MetaFox\Quiz\Models\Answer,id'],
            'questions.*.answers.*.answer'     => ['required_with:questions.*.answers.*', 'string', 'between:1,255'],
            'questions.*.answers.*.is_correct' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'item_id'                          => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'file'                             => [$fileRequiredRule, 'array', 'nullable'],
            'file.temp_file'                   => ['required_if:file.status,' . MetaFoxConstant::FILE_UPDATE_STATUS, 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'file.status'                      => ['required_with:file.temp_file', 'string', new AllowInRule([MetaFoxConstant::FILE_UPDATE_STATUS, MetaFoxConstant::FILE_REMOVE_STATUS])],
            'privacy'                          => ['sometimes', new PrivacyRule([
                'validate_privacy_list' => false,
            ])],
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
        $data = data_get($this->validator->validated(), $key, $default);

        $data = $this->handlePrivacy($data);

        $data = $this->transformQuestionData($data);

        if (isset($data['file']['temp_file'])) {
            $data['temp_file'] = $data['file']['temp_file'];
        }

        if (isset($data['file']['status'])) {
            $data['remove_photo'] = true;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function transformQuestionData(array $data): array
    {
        if (!Arr::has($data, 'questions')) {
            return $data;
        }

        $rawQuestions = Arr::get($data, 'questions') ?? [];

        $formattedQuestion = [];

        $questionOrdering = $answerOrdering = 0;

        foreach ($rawQuestions as $question) {
            // Set question ordering
            $question['ordering'] = ++$questionOrdering;
            $formattedAnswer      = [];

            foreach ($question['answers'] as $answer) {
                // Set default is_correct as false when it is not set
                if (!isset($answer['is_correct'])) {
                    $answer['is_correct'] = 0;
                }

                // Set answer ordering
                $answer['ordering'] = ++$answerOrdering;

                if (isset($answer['id'])) {
                    $formattedAnswer['changedAnswers'][$answer['id']] = $answer;
                    continue;
                }
                $formattedAnswer['newAnswers'][] = $answer;
            }
            $question['answers'] = $formattedAnswer;

            if (isset($question['id'])) {
                $formattedQuestion['changedQuestions'][$question['id']] = $question;
                continue;
            }
            $formattedQuestion['newQuestions'][] = $question;
        }

        $data['questions'] = $formattedQuestion;

        if (!isset($data['questions']['changedQuestions'])) {
            $data['questions']['changedQuestions'] = [];
        }

        return $data;
    }
}
