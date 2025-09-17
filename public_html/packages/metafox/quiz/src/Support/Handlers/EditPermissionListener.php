<?php

namespace MetaFox\Quiz\Support\Handlers;

use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Yup\Yup;

class EditPermissionListener
{
    public static function maxQuestionPerQuiz(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->asNumber()
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->required()
                    ->unint()
                    ->min(1)
                    ->when(
                        Yup::when('min_question_quiz')
                            ->is('$exists')
                            ->then(Yup::number()->min(['ref' => 'min_question_quiz']))
                    )
            );
    }

    public static function minQuestionPerQuiz(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->asNumber()
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->required()
                    ->unint()
                    ->min(1)
            );
    }

    public static function maxAnswerPerQuiz(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->asNumber()
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->required()
                    ->unint()
                    ->min(1)
                    ->when(
                        Yup::when('min_answer_question_quiz')
                            ->is('$exists')
                            ->then(Yup::number()->min(['ref' => 'min_answer_question_quiz']))
                    )
            );
    }

    public static function minAnswerPerQuiz(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->asNumber()
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->required()
                    ->unint()
                    ->min(2)
            );
    }

    public static function defaultAnswerPerQuiz(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->asNumber()
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->required()
                    ->unint()
                    ->min(2)
                    ->when(
                        Yup::when('min_answer_question_quiz')
                            ->is('$exists')
                            ->then(Yup::number()->min(['ref' => 'min_answer_question_quiz']))
                    )
                    ->when(
                        Yup::when('max_answer_question_quiz')
                            ->is('$exists')
                            ->then(Yup::number()->max(['ref' => 'max_answer_question_quiz']))
                    )
            );
    }
}
