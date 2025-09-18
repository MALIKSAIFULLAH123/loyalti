<?php

namespace MetaFox\Group\Http\Resources\v1\ExampleRule\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Group\Models\ExampleRule as Model;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreExampleRuleForm.
 * @property ?Model $resource
 */
class StoreExampleRuleForm extends AbstractForm
{
    protected const MAX_LENGTH_DESCRIPTION = 500;
    /** @var bool */
    protected $isEdit = false;

    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('group::phrase.create_example_group_rule'))
            ->action(url_utility()->makeApiUrl('admincp/group/example-rule'))
            ->setValue([
                'is_active' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $basic           = $this->addBasic([]);
        $maxLengthTitle  = MetaFoxConstant::CHARACTER_LIMIT;

        $basic->addFields(
            Builder::translatableText('title')
            ->label(__p('core::phrase.title'))
                ->description(__p('group::phrase.maximum_length_of_characters', ['length' => $maxLengthTitle]))
            ->required()
            ->maxLength($maxLengthTitle)
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
            )
            ->buildFields(),
            Builder::translatableText('description')
            ->label(__p('core::phrase.description'))
            ->required()
            ->maxLength(self::MAX_LENGTH_DESCRIPTION)
            ->yup(
                Yup::string()
                    ->maxLength(
                        self::MAX_LENGTH_DESCRIPTION,
                        __p('validation.field_must_be_at_most_max_length_characters', [
                            'field'     => __p('core::phrase.description'),
                            'maxLength' => self::MAX_LENGTH_DESCRIPTION,
                        ])
                    )
                    ->required(__p('validation.this_field_is_a_required_field'))
            )
            ->buildFields(),
            Builder::checkbox('is_active')
                ->label(__p('core::phrase.is_active'))
        );

        $this->addDefaultFooter();
    }
}
