<?php

namespace MetaFox\AntiSpamQuestion\Listeners;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use MetaFox\AntiSpamQuestion\Models\Question;
use MetaFox\AntiSpamQuestion\Repositories\QuestionAdminRepositoryInterface;
use MetaFox\AntiSpamQuestion\Rules\AntiSpamAnswerRule;
use MetaFox\AntiSpamQuestion\Support\Constants;
use MetaFox\Platform\Facades\Settings;

class AntiSpamQuestionRegistrationFieldRulesListener
{
    public function __construct(protected QuestionAdminRepositoryInterface $repository) {}

    public function handle(\ArrayObject $rules): void
    {
        if (!Settings::get('antispamquestion.enable_spam_question_on_signup', false)) {
            return;
        }

        $requireAll = Settings::get('antispamquestion.require_all_spam_questions_on_signup', false);

        /** @var Collection $questions */
        $questions = $this->repository->cacheActiveQuestions();

        if ($questions->isEmpty()) {
            return;
        }

        if ($requireAll) {
            foreach ($questions as $question) {
                /** @var Question $question */
                $rules[$this->getFieldName($question->entityId())] = $this->buildAnswerRules($question);
            }
            return;
        }

        $questionId = request()->get(Constants::QUESTION_ID_KEY);

        try {
            $randomQuestion = $this->repository->find($questionId);
        } catch (\Exception $exception) {
            Log::debug(__CLASS__ . ': random question not found');
            return;
        }

        if (!$randomQuestion->is_active) {
            Log::debug(__CLASS__ . ': random question not active');
            return;
        }

        $rules[$this->getFieldName($randomQuestion->entityId())] = $this->buildAnswerRules($randomQuestion);
    }

    private function buildAnswerRules(Question $question): array
    {
        return ['required', 'string', new AntiSpamAnswerRule($question)];
    }

    protected function getFieldName(string $name): string
    {
        return sprintf(Constants::PREFIX_ASQ_QUESTION_FIELD, $name);
    }
}
