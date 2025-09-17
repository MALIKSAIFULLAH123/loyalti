<?php

namespace MetaFox\User\Http\Resources\v1\UserRelation\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Models\UserRelation as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateForm.
 * @property Model $resource
 */
class CreateForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('user::phrase.add_new_relation'))
            ->action('/admincp/user/relation')
            ->asPost()
            ->setValue([
                'is_active' => 0,
                'is_custom' => 1,
            ]);
    }

    protected function initialize(): void
    {
        $info           = $this->addSection(['name' => 'info']);
        $maxLengthTitle = MetaFoxConstant::CHARACTER_LIMIT;

        $info->addFields(
            Builder::translatableText('phrase_var')
                ->label(__p('core::phrase.title'))
                ->required()
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->maxLength(
                            $maxLengthTitle,
                            __p('validation.field_must_be_at_most_max_length_characters', [
                                'field'     => __p('core::phrase.title'),
                                'maxLength' => $maxLengthTitle,
                            ])
                        )
                )->buildFields(),
            Builder::singlePhoto()
                ->previewUrl($this->resource?->avatar)
                ->required()
                ->label(__p('user::phrase.profile_image'))
                ->placeholder(__p('user::phrase.profile_image'))
                ->description(__p('user::phrase.profile_image_desc'))
                ->yup(
                    Yup::object()
                        ->addProperty('id', [
                            'type'     => 'number',
                            'required' => true,
                            'errors'   => [
                                'required' => __p('user::validation.profile_image_is_a_required_field'),
                            ],
                        ])
                ),
            Builder::checkbox('is_active')
                ->label(__p('core::phrase.is_active')),
        );

        /// keep footer

        $this->addDefaultFooter($this->isEdit());
    }

    protected function isEdit(): bool
    {
        return false;
    }
}
