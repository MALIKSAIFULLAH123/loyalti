<?php

namespace MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
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
class StoreQuestionForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('antispamquestion::phrase.create_question'))
            ->action(apiUrl('admin.antispamquestion.question.store'))
            ->asPost()->setValue([
                'is_active'         => true,
                'is_case_sensitive' => false,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::translatableText('question')
                    ->required()
                    ->label(__p('antispamquestion::phrase.question'))
                    ->yup(Yup::string()->required())
                    ->buildFields(),
                Builder::singlePhoto('file')
                    ->itemType('antispamquestion')
                    ->previewUrl($this->resource?->image_file_id ? $this->resource?->image : ''),
                Builder::checkbox('is_active')
                    ->label(__p('core::phrase.is_active'))
                    ->yup(Yup::boolean()),
                Builder::checkbox('is_case_sensitive')
                    ->label(__p('antispamquestion::phrase.case_sensitive'))
                    ->yup(Yup::boolean()),
                Builder::freeOptions('answers')
                    ->required()
                    ->label(__p('antispamquestion::phrase.answers'))
                    ->minLength(1)
                    ->yup(Yup::array()
                        ->min(1)
                        ->required()
                        ->of(
                            Yup::object()
                                ->addProperty('ordering', Yup::number())
                                ->addProperty('value', Yup::string()
                                    ->maxLength(MetaFoxConstant::CHARACTER_LIMIT, __p('core::validation.field_maximum_length_of_characters', [
                                        'field' => __p('antispamquestion::phrase.answers'),
                                        'max'   => MetaFoxConstant::CHARACTER_LIMIT,
                                        'min'   => 1,
                                    ]))
                                    ->required(__p('core::validation.this_field_is_a_required_field')))
                                ->addProperty('status', Yup::string())
                        )),
            );

        $this->addDefaultFooter();
    }
}
