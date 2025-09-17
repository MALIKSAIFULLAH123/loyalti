<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz\Field;

use MetaFox\Form\AbstractField;
use MetaFox\Platform\Facades\Settings;

class QuizQuestion extends AbstractField
{
    public function initialize(): void
    {
        $context      = user();
        $maxQuestions = $context->hasSuperAdminRole() ? 0 : (int) $context->getPermissionValue('quiz.max_question_quiz');
        $maxAnswers   = $context->hasSuperAdminRole() ? 0 : (int) $context->getPermissionValue('quiz.max_answer_question_quiz');

        $this->setAttributes([
            'component'       => 'QuizQuestion',
            'name'            => 'questions',
            'variant'         => 'outlined',
            'fullWidth'       => true,
            'minQuestions'    => (int) $context->getPermissionValue('quiz.min_question_quiz'),
            'maxQuestions'    => $maxQuestions,
            'minAnswers'      => (int) $context->getPermissionValue('quiz.min_answer_question_quiz'),
            'maxAnswers'      => $maxAnswers,
            'defaultAnswers'  => (int) $context->getPermissionValue('quiz.number_of_answers_per_default'),
            'maxAnswerLength' => Settings::get('quiz.maximum_quiz_answer_length', 255),
            'minAnswerLength' => Settings::get('quiz.minimum_quiz_answer_length', 3),
            'returnKeyType'   => 'next',
        ]);
    }
}
