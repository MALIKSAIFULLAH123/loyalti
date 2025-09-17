<?php

namespace MetaFox\Profile\Http\Resources\v1\Section\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Models\Section as Model;
use MetaFox\Profile\Traits\CreateSectionFormTrait;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditSectionForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditSectionForm extends AbstractForm
{
    use CreateSectionFormTrait;

    protected function prepare(): void
    {
        $values = $this->getValues();

        Arr::set($values, 'is_active', $this->resource->is_active);

        $this->title(__p('core::phrase.edit'))
            ->action('/admincp/profile/section/' . $this->resource?->id)
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('name')
                    ->required()
                    ->label(__p('core::phrase.name'))
                    ->disabled($this->isDisableFieldName())
                    ->yup(
                        Yup::string()
                            ->required()
                            ->matches(MetaFoxConstant::RESOURCE_IDENTIFIER_REGEX, __p('validation.alpha_underscore_lower_only', [
                                'attribute' => '${path}',
                            ]))
                    ),
                Builder::translatableText('label')
                    ->label(__p('core::phrase.label'))
                    ->required()
                    ->buildFields(),
                Builder::checkbox('is_active')
                    ->disabled($this->isDisableFieldName())
                    ->label(__p('core::phrase.is_active')),
            );

        $this->addDefaultFooter();
    }

    public function isEdit(): bool
    {
        return true;
    }

    public function isDisableFieldName(): bool
    {
        return $this->resource?->is_system ?? false;
    }
}
