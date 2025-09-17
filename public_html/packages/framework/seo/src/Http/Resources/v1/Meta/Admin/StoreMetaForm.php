<?php

namespace MetaFox\SEO\Http\Resources\v1\Meta\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\SEO\Models\Meta as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreMetaForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreMetaForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('core::phrase.create'))
            ->action('/admincp/seo/meta')
            ->asPost()
            ->setValue([
                'package_id' => 'metafox/core',
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::selectPackage('package_id'),
            Builder::text('name')
                ->required()
                ->label('Name')
                ->placeholder('Page Name'),
            Builder::text('title')
                ->required()
                ->label('Title')
                ->placeholder('Page Title'),
            Builder::text('secondary_menu')
                ->required()
                ->label('Secondary Menu'),
        );

        $this->addDefaultFooter();
    }
}
