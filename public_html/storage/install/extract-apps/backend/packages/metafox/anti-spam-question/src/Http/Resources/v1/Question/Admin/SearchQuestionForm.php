<?php

namespace MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 *  Form Resource
 * --------------------------------------------------------------------------.
 *
 * This stub is used by MetaFox Generator.
 * Please complete it to treat as model resource.
 */

/**
 * @property mixed $data
 */
class SearchQuestionForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('antispamquestion/question/browse')
            ->acceptPageParams(['q', 'created_from', 'created_to'])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->setValue([
                'created_from' => null,
                'created_to'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()->asHorizontal()->sxContainer(['alignItems' => 'unset'])
            ->addFields(
                Builder::text('q')
                    ->label(__p('core::phrase.title'))
                    ->placeholder(__p('core::phrase.title'))
                    ->fullWidth()
                    ->sizeSmall()
                    ->sxFieldWrapper(['maxWidth' => 220])
                    ->marginDense(),
                Builder::yesNoChoice('is_active')
                    ->label(__p('core::phrase.is_active'))
                    ->fullWidth()
                    ->sizeSmall()
                    ->sxFieldWrapper(['maxWidth' => 220])
                    ->marginDense(),
                Builder::yesNoChoice('is_case_sensitive')
                    ->label(__p('antispamquestion::phrase.case_sensitive'))
                    ->fullWidth()
                    ->sizeSmall()
                    ->sxFieldWrapper(['maxWidth' => 220])
                    ->marginDense(),
                Builder::date('created_from')
                    ->label(__p('core::phrase.created_from'))
                    ->startOfDay()
                    ->forAdminSearchForm()
                    ->yup(Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.created_from')]))),
                Builder::date('created_to')
                    ->label(__p('core::phrase.created_to'))
                    ->endOfDay()
                    ->forAdminSearchForm()
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->min(['ref' => 'created_from'])
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.created_to')]))
                            ->setError('min', __p('validation.the_end_time_should_be_greater_than_the_start_time', [
                                'end_time'   => __p('core::phrase.created_to'),
                                'start_time' => __p('core::phrase.created_from'),
                            ]))
                    ),
                Builder::submit()
                    ->forAdminSearchForm(),
                Builder::clearSearchForm()
                    ->label(__p('core::phrase.reset'))
                    ->align('center')
                    ->forAdminSearchForm()
                    ->sizeMedium(),
            );
    }
}
