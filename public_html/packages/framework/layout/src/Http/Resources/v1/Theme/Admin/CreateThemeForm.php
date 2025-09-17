<?php

namespace MetaFox\Layout\Http\Resources\v1\Theme\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Layout\Models\Theme as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateThemeForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateThemeForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.layout.theme.store'))
            ->description(__p('layout::phrase.create_theme_form_guide'))
            ->asPost()
            ->setValue([
                'theme_id' => '',
                'vendor'   => '',
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('vendor')
                    ->required()
                    ->label(__p('core::phrase.vendor_name'))
                    ->placeholder('etc: metafox')
                    ->forceLabelShrink()
                    ->yup(Yup::string()->required()
                        ->minLength(3)
                        ->maxLength(32)
                        ->matches("^\w+$")
                    ),
                Builder::text('theme_id')
                    ->required()
                    ->label(__p('core::phrase.id'))
                    ->startAdornment('theme-')
                    ->description(__p('layout::phrase.create_theme_id_guide'))
                    ->yup(Yup::string()
                        ->required()
                        ->minLength(3)
                        ->maxLength(32)
                        ->matches("^\w+$")
                    ),
                Builder::text('title')
                    ->required()
                    ->forceLabelShrink()
                    ->label(__p('core::phrase.name'))
                    ->description(__p('layout::phrase.title_desc'))
                    ->yup(Yup::string()->required()),
            );

        $this->addDefaultFooter();
    }
}
