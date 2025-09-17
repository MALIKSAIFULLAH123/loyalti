<?php

namespace MetaFox\AntiSpamQuestion\Listeners;

use Illuminate\Support\Arr;
use MetaFox\AntiSpamQuestion\Models\Question;
use MetaFox\AntiSpamQuestion\Repositories\QuestionAdminRepositoryInterface;
use MetaFox\AntiSpamQuestion\Support\Constants;
use MetaFox\Form\Builder;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Yup\Yup;

class AntiSpamQuestionRegistrationFieldsListener
{
    public function __construct(protected QuestionAdminRepositoryInterface $repository) {}

    public function handle(Section $section): void
    {
        if (!Settings::get('antispamquestion.enable_spam_question_on_signup', false)) {
            return;
        }

        $requireAll = Settings::get('antispamquestion.require_all_spam_questions_on_signup', false);
        $questions  = $this->repository->cacheActiveQuestions();

        if ($questions->isEmpty()) {
            return;
        }

        if ($requireAll) {
            foreach ($questions as $question) {
                $this->buildQuestionField($section, $question);
            }
            return;
        }

        $randomQuestion = $questions->random();
        $builder        = $this->getBuilder();

        $this->buildQuestionField($section, $randomQuestion);
        $section->addField(
            $builder::hidden(Constants::QUESTION_ID_KEY)
                ->setValue($randomQuestion->entityId())
        );
    }

    private function buildQuestionField(Section $section, Question $question): void
    {
        $builder    = $this->getBuilder();
        $imageProps = [
            'src' => $question->image,
        ];

        if (!MetaFox::isMobile()) {
            Arr::set($imageProps, 'aspectRatio', '169');
            Arr::set($imageProps, 'imageFit', 'contain');
        }

        $section->addField(
            $builder::text($this->getFieldName($question->entityId()))
                ->required()
                ->hasFormLabel(true)
                ->setAttribute('imageProps', $imageProps)
                ->label($question->toTitle())
                ->placeholder(__p('antispamquestion::phrase.enter_your_answer'))
                ->description($question->is_case_sensitive
                    ? __p('antispamquestion::phrase.this_question_will_check_the_answer_in_a_case_sensitive')
                    : null)
                ->yup(Yup::string()->required())
        );
    }

    protected function getBuilder(): MobileBuilder|Builder
    {
        return MetaFox::isMobile()
            ? new MobileBuilder()
            : new Builder();
    }

    protected function getFieldName(string $name): string
    {
        return sprintf(Constants::PREFIX_ASQ_QUESTION_FIELD, $name);
    }
}
