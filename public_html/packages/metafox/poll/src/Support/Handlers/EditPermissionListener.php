<?php

namespace MetaFox\Poll\Support\Handlers;

use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Yup\Yup;

class EditPermissionListener
{
    public static function maximumAnswersCount(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->required()
                    ->min(2)
                    ->positive()
                    ->int(__p('poll::phrase.maximum_answers_count_error'))
                    ->setError('typeError', __p('poll::validation.type_error_poll_maximum_answer'))
            );
    }
}
