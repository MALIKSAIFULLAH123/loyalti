<?php

namespace MetaFox\Localize\Http\Resources\v1\Language\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Localize\Models\Language as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ImportLanguageForm.
 * @property Model $resource
 */
class ImportLanguageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('admincp/language')
            ->asPost();
    }

    protected function initialize(): void
    {
        $this->addDefaultFooter();
    }
}
