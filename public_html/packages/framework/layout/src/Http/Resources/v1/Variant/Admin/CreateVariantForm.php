<?php

namespace MetaFox\Layout\Http\Resources\v1\Variant\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Layout\Models\Theme as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateVariantForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateVariantForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.layout.variant.store'))
            ->description('layout::phrase.create_variant_description_guide')
            ->asPost()
            ->setValue([
                'variant_id' => uniqid('f'),
                'theme_id'   => $this->resource->theme_id,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('variant_id')
                    ->required()
                    ->label(__p('core::phrase.id'))
                    ->description(__p('layout::phrase.create_variant_id_guide'))
                    ->yup(Yup::string()),
                Builder::text('theme_id')
                    ->required()
                    ->disabled()
                    ->label(__p('layout::phrase.theme'))
                    ->yup(Yup::string()),
                Builder::text('title')
                    ->required()
                    ->label(__p('core::phrase.title'))
                    ->yup(Yup::string()),
            );

        $this->addDefaultFooter(false);
    }
}
